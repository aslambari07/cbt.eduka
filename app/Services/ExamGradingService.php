<?php

namespace App\Services;

use App\Enums\ExamResultStatus;
use App\Enums\ExamSessionStatus;
use App\Enums\QuestionType;
use App\Enums\Role;
use App\Models\ExamResult;
use App\Models\ExamResultDetail;
use App\Models\ExamSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamGradingService
{
    public function __construct(private readonly QuestionTypeRegistry $registry, private readonly AuditLogService $auditLog) {}

    public function grade(ExamSession $session): ExamResult
    {
        if (! in_array($session->status, [ExamSessionStatus::SUBMITTED, ExamSessionStatus::TIME_EXPIRED, ExamSessionStatus::COMPLETED], true)) {
            throw ValidationException::withMessages(['session' => 'Sesi belum disubmit.']);
        }

        return DB::transaction(function () use ($session): ExamResult {
            $session = ExamSession::query()->with(['exam.questions.options', 'answers'])->lockForUpdate()->findOrFail($session->id);
            $result = ExamResult::query()->firstOrCreate(
                ['exam_session_id' => $session->id],
                ['exam_id' => $session->exam_id, 'student_id' => $session->student_id, 'passing_grade' => $session->exam->passing_grade, 'status' => ExamResultStatus::PENDING]
            );
            $existingDetails = $result->details()->get()->keyBy('question_id');
            $answers = $session->answers->keyBy('question_id');
            $total = 0.0;

            foreach ($session->exam->questions as $question) {
                $max = (float) ($question->pivot->points ?? $question->points);
                $total += $max;
                $answer = $answers->get($question->id);
                $existing = $existingDetails->get($question->id);
                if ($question->type === QuestionType::ESSAY && $existing?->grading_status === 'graded') {
                    $score = (float) $existing->earned_points;
                    $status = 'graded';
                    $feedback = $existing->feedback;
                } elseif ($question->type === QuestionType::ESSAY) {
                    $score = 0.0;
                    $status = 'manual_pending';
                    $feedback = 'Menunggu penilaian manual.';
                } else {
                    $scoreResult = $this->registry->for($question->type)->score($answer?->answer, $question->definition ?? [], $max);
                    $score = $scoreResult->score;
                    $status = 'auto_graded';
                    $feedback = $scoreResult->feedback;
                }
                $result->details()->updateOrCreate(
                    ['question_id' => $question->id],
                    ['exam_answer_id' => $answer?->id, 'max_points' => $max, 'earned_points' => $score, 'grading_status' => $status, 'feedback' => $feedback]
                );
            }

            $earned = (float) $result->details()->sum('earned_points');
            $hasPending = $result->details()->where('grading_status', 'manual_pending')->exists();
            $score = $total > 0 ? round($earned / $total * 100, 2) : 0.0;
            $result->update(['total_points' => $total, 'earned_points' => $earned, 'score' => $score, 'is_passed' => $score >= (float) $result->passing_grade, 'status' => $hasPending ? ExamResultStatus::PENDING : ExamResultStatus::GRADED, 'graded_at' => $hasPending ? null : now()]);
            $session->forceFill(['status' => ExamSessionStatus::COMPLETED])->save();
            $session->assignment()->update(['status' => 'completed']);

            return $result->fresh(['details', 'exam']);
        });
    }

    public function gradeManual(User $grader, ExamResultDetail $detail, float $score, ?string $feedback = null): ExamResult
    {
        $detail->load('result.exam');
        if (! $grader->isActive() || ! $grader->hasRole([Role::ADMIN, Role::TEACHER]) || (! $grader->hasRole(Role::ADMIN) && $detail->result->exam->owner_id !== $grader->id)) {
            throw ValidationException::withMessages(['grader' => 'Anda tidak memiliki akses menilai hasil ini.']);
        }
        if ($score < 0 || $score > (float) $detail->max_points) {
            throw ValidationException::withMessages(['score' => 'Nilai harus berada di antara 0 dan nilai maksimum.']);
        }
        if ($detail->result->status === ExamResultStatus::PUBLISHED) {
            throw ValidationException::withMessages(['result' => 'Hasil yang sudah dipublikasikan memerlukan proses koreksi terkontrol.']);
        }

        return DB::transaction(function () use ($detail, $grader, $score, $feedback): ExamResult {
            $detail->update(['earned_points' => $score, 'grading_status' => 'graded', 'feedback' => $feedback, 'graded_by' => $grader->id, 'graded_at' => now()]);
            $this->auditLog->record($grader, 'result.detail_graded', $detail, ['score' => $score]);
            $result = $detail->result()->lockForUpdate()->firstOrFail();
            $earned = (float) $result->details()->sum('earned_points');
            $pending = $result->details()->where('grading_status', 'manual_pending')->exists();
            $scorePercent = (float) $result->total_points > 0 ? round($earned / (float) $result->total_points * 100, 2) : 0.0;
            $result->update(['earned_points' => $earned, 'score' => $scorePercent, 'is_passed' => $scorePercent >= (float) $result->passing_grade, 'status' => $pending ? ExamResultStatus::PENDING : ExamResultStatus::GRADED, 'graded_at' => $pending ? null : now()]);

            return $result->fresh(['details', 'exam']);
        });
    }
}
