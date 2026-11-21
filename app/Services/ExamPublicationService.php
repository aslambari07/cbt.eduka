<?php

namespace App\Services;

use App\Enums\ExamResultStatus;
use App\Enums\Role;
use App\Models\ExamResult;
use App\Models\User;
use App\Notifications\ResultPublishedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamPublicationService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function publish(User $actor, ExamResult $result): ExamResult
    {
        $result->load('exam');
        if (! $actor->isActive() || ! $actor->hasRole([Role::ADMIN, Role::TEACHER]) || (! $actor->hasRole(Role::ADMIN) && $result->exam->owner_id !== $actor->id)) {
            throw ValidationException::withMessages(['result' => 'Anda tidak memiliki akses mempublikasikan hasil ini.']);
        }
        if ($result->status !== ExamResultStatus::GRADED) {
            throw ValidationException::withMessages(['result' => 'Hasil belum selesai dinilai.']);
        }

        return $this->markPublished($result, $actor, 'result.published');
    }

    public function publishAutomatically(ExamResult $result): ExamResult
    {
        $result->load('exam');
        if ($result->status !== ExamResultStatus::GRADED || $result->exam->results_visibility !== 'immediate') {
            return $result;
        }

        return $this->markPublished($result, null, 'result.published_automatically');
    }

    private function markPublished(ExamResult $result, ?User $actor, string $auditAction): ExamResult
    {
        return DB::transaction(function () use ($actor, $result, $auditAction): ExamResult {
            $locked = ExamResult::query()->lockForUpdate()->findOrFail($result->id);
            if ($locked->status === ExamResultStatus::PUBLISHED) {
                return $locked->fresh(['exam', 'student', 'details']);
            }
            $locked->update(['status' => ExamResultStatus::PUBLISHED, 'published_at' => now()]);
            $locked->student->notify(new ResultPublishedNotification($locked->fresh('exam')));
            $this->auditLog->record($actor, $auditAction, $locked, ['score' => (float) $locked->score]);

            return $locked->fresh(['exam', 'student', 'details']);
        });
    }
}
