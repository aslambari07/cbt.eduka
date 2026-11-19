<?php

namespace App\Services;

use App\Enums\ExamAssignmentStatus;
use App\Enums\ExamSessionStatus;
use App\Enums\ExamStatus;
use App\Enums\Role;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAssignment;
use App\Models\ExamFlag;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ExamSessionService
{
    public function __construct(private readonly AnswerPayloadValidator $answerValidator) {}

    public function start(User $student, Exam $exam): ExamSession
    {
        $this->ensureStudent($student);
        $now = now();

        if (! in_array($exam->status, [ExamStatus::PUBLISHED, ExamStatus::ONGOING], true)) {
            throw ValidationException::withMessages(['exam' => 'Ujian belum dapat dimulai.']);
        }
        if ($exam->starts_at && $now->lessThan($exam->starts_at)) {
            throw ValidationException::withMessages(['exam' => 'Ujian belum memasuki waktu mulai.']);
        }
        if ($exam->ends_at && $now->greaterThanOrEqualTo($exam->ends_at)) {
            throw ValidationException::withMessages(['exam' => 'Ujian sudah berakhir.']);
        }

        return DB::transaction(function () use ($student, $exam, $now): ExamSession {
            $assignment = ExamAssignment::query()->where('exam_id', $exam->id)->where('student_id', $student->id)->lockForUpdate()->first();
            if (! $assignment) {
                throw ValidationException::withMessages(['exam' => 'Ujian ini tidak ditugaskan kepada Anda.']);
            }

            $active = ExamSession::query()->where('assignment_id', $assignment->id)->whereNotIn('status', [ExamSessionStatus::SUBMITTED->value, ExamSessionStatus::COMPLETED->value, ExamSessionStatus::TIME_EXPIRED->value])->latest('attempt_number')->first();
            if ($active) {
                return $active;
            }

            $attempt = ((int) ExamSession::query()->where('assignment_id', $assignment->id)->max('attempt_number')) + 1;
            if ($attempt > $assignment->max_attempts) {
                throw ValidationException::withMessages(['exam' => 'Batas percobaan ujian telah tercapai.']);
            }

            $questions = $exam->questions()->with('options')->get();
            if ($questions->isEmpty()) {
                throw ValidationException::withMessages(['exam' => 'Ujian belum memiliki soal.']);
            }

            $questionOrder = $questions->pluck('id')->values()->all();
            if ($exam->randomize_questions) {
                shuffle($questionOrder);
            }
            $optionOrders = [];
            foreach ($questions as $question) {
                $keys = $question->options->pluck('key')->values()->all();
                if ($exam->randomize_options) {
                    shuffle($keys);
                }
                $optionOrders[(string) $question->id] = $keys;
            }

            $expiresAt = $now->copy()->addMinutes($exam->duration_minutes);
            if ($exam->ends_at && $exam->ends_at->lessThan($expiresAt)) {
                $expiresAt = $exam->ends_at->copy();
            }

            $session = ExamSession::create([
                'session_key' => (string) Str::uuid(),
                'exam_id' => $exam->id,
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
                'attempt_number' => $attempt,
                'status' => ExamSessionStatus::IN_PROGRESS,
                'started_at' => $now,
                'expires_at' => $expiresAt,
                'last_seen_at' => $now,
                'question_order' => $questionOrder,
                'option_orders' => $optionOrders,
            ]);
            $assignment->update(['status' => ExamAssignmentStatus::STARTED]);
            $this->event($session, 'exam_started', $student, ['attempt' => $attempt]);

            return $session;
        });
    }

    public function payload(ExamSession $session, User $student): array
    {
        $this->ensureOwner($session, $student);
        $session->load(['exam.questions.options', 'answers', 'flags']);
        $questions = $session->exam->questions->keyBy('id');
        $answers = $session->answers->keyBy('question_id');
        $flags = $session->flags->pluck('question_id')->map(fn ($id) => (int) $id)->all();
        $ordered = [];

        foreach ($session->question_order ?? $questions->keys()->all() as $questionId) {
            $question = $questions->get((int) $questionId);
            if (! $question) {
                continue;
            }
            $rendered = $question->publicRenderData();
            $optionOrder = $session->option_orders[(string) $question->id] ?? [];
            if ($optionOrder !== []) {
                $options = collect($rendered['options'])->keyBy('key');
                $rendered['options'] = collect($optionOrder)->map(fn ($key) => $options->get($key))->filter()->values()->all();
            }
            $rendered['answer'] = $answers->get($question->id)?->answer;
            $rendered['answer_version'] = $answers->get($question->id)?->version ?? 0;
            $rendered['flagged'] = in_array($question->id, $flags, true);
            $ordered[] = $rendered;
        }

        return [
            'session_key' => $session->session_key,
            'status' => $session->status->value,
            'server_now' => now()->toIso8601String(),
            'started_at' => $session->started_at?->toIso8601String(),
            'expires_at' => $session->expires_at?->toIso8601String(),
            'remaining_seconds' => $session->expires_at ? max(0, now()->diffInSeconds($session->expires_at, false)) : null,
            'questions' => $ordered,
        ];
    }

    public function saveAnswer(ExamSession $session, User $student, Question $question, mixed $answer, int $clientVersion = 0): ExamAnswer
    {
        $this->ensureOwner($session, $student);
        $this->ensureOpen($session);
        if (! $session->exam->questions()->whereKey($question->id)->exists()) {
            throw ValidationException::withMessages(['question' => 'Soal tidak termasuk dalam sesi ujian.']);
        }
        $normalized = $this->answerValidator->validateAndNormalize($question->loadMissing('options'), $answer);

        return DB::transaction(function () use ($session, $student, $question, $normalized, $clientVersion): ExamAnswer {
            $existing = ExamAnswer::query()->where('exam_session_id', $session->id)->where('question_id', $question->id)->lockForUpdate()->first();
            if ($existing && $existing->version > $clientVersion) {
                return $existing;
            }
            $nextVersion = ($existing?->version ?? 0) + 1;
            $answerModel = $existing ?? new ExamAnswer(['exam_session_id' => $session->id, 'question_id' => $question->id]);
            $answerModel->fill(['answer' => $normalized, 'version' => $nextVersion, 'status' => 'saved', 'grading_status' => 'pending', 'saved_at' => now()]);
            $answerModel->save();
            $session->forceFill(['status' => ExamSessionStatus::SAVED, 'last_seen_at' => now(), 'version' => $session->version + 1])->save();
            $this->event($session, 'answer_saved', $student, ['question_id' => $question->id, 'version' => $nextVersion]);

            return $answerModel->refresh();
        });
    }

    public function toggleFlag(ExamSession $session, User $student, Question $question): bool
    {
        $this->ensureOwner($session, $student);
        $this->ensureOpen($session);
        if (! $session->exam->questions()->whereKey($question->id)->exists()) {
            throw ValidationException::withMessages(['question' => 'Soal tidak termasuk dalam sesi ujian.']);
        }
        $flag = ExamFlag::query()->where('exam_session_id', $session->id)->where('question_id', $question->id)->first();
        if ($flag) {
            $flag->delete();
            $this->event($session, 'question_unflagged', $student, ['question_id' => $question->id]);

            return false;
        }
        ExamFlag::create(['exam_session_id' => $session->id, 'question_id' => $question->id]);
        $this->event($session, 'question_flagged', $student, ['question_id' => $question->id]);

        return true;
    }

    public function submit(ExamSession $session, User $student): ExamSession
    {
        $this->ensureOwner($session, $student);
        if (in_array($session->status, [ExamSessionStatus::SUBMITTED, ExamSessionStatus::COMPLETED], true)) {
            return $session;
        }
        if ($session->expires_at && now()->greaterThanOrEqualTo($session->expires_at)) {
            return $this->expire($session, $student);
        }

        return DB::transaction(function () use ($session, $student): ExamSession {
            $locked = ExamSession::query()->whereKey($session->id)->lockForUpdate()->firstOrFail();
            if (in_array($locked->status, [ExamSessionStatus::SUBMITTED, ExamSessionStatus::COMPLETED], true)) {
                return $locked;
            }
            $locked->forceFill(['status' => ExamSessionStatus::SUBMITTED, 'submitted_at' => now(), 'last_seen_at' => now(), 'version' => $locked->version + 1])->save();
            $locked->assignment()->update(['status' => ExamAssignmentStatus::SUBMITTED]);
            $this->event($locked, 'exam_submitted', $student);

            return $locked->refresh();
        });
    }

    public function expire(ExamSession $session, User $student): ExamSession
    {
        $this->ensureOwner($session, $student);
        $session->forceFill(['status' => ExamSessionStatus::TIME_EXPIRED, 'submitted_at' => $session->submitted_at ?? now(), 'last_seen_at' => now()])->save();
        $session->assignment()->update(['status' => ExamAssignmentStatus::EXPIRED]);
        $this->event($session, 'time_expired', $student);

        return $session->refresh();
    }

    private function ensureStudent(User $student): void
    {
        if (! $student->isActive() || ! $student->hasRole(Role::STUDENT)) {
            throw ValidationException::withMessages(['student' => 'Hanya peserta aktif yang dapat memulai ujian.']);
        }
    }

    private function ensureOwner(ExamSession $session, User $student): void
    {
        $this->ensureStudent($student);
        if ((int) $session->student_id !== (int) $student->id) {
            throw ValidationException::withMessages(['session' => 'Sesi ujian tidak dimiliki oleh peserta ini.']);
        }
    }

    private function ensureOpen(ExamSession $session): void
    {
        if (in_array($session->status, [ExamSessionStatus::SUBMITTED, ExamSessionStatus::COMPLETED, ExamSessionStatus::TIME_EXPIRED], true)) {
            throw ValidationException::withMessages(['session' => 'Sesi ujian sudah dikunci.']);
        }
        if ($session->expires_at && now()->greaterThanOrEqualTo($session->expires_at)) {
            $session->forceFill(['status' => ExamSessionStatus::TIME_EXPIRED, 'submitted_at' => $session->submitted_at ?? now()])->save();
            throw ValidationException::withMessages(['session' => 'Waktu ujian telah habis.']);
        }
    }

    private function event(ExamSession $session, string $type, ?User $actor = null, array $payload = []): void
    {
        $session->events()->create(['actor_id' => $actor?->id, 'type' => $type, 'payload' => $payload, 'occurred_at' => now()]);
    }
}
