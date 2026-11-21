<?php

namespace App\Services;

use App\Enums\ExamResultStatus;
use App\Enums\Role;
use App\Models\Certificate;
use App\Models\ExamResult;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CertificateService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function issue(ExamResult $result): Certificate
    {
        $result->load(['exam', 'student']);
        if ($result->status !== ExamResultStatus::PUBLISHED || ! $result->is_passed) {
            throw ValidationException::withMessages(['certificate' => 'Sertifikat hanya tersedia untuk hasil lulus yang sudah dipublikasikan.']);
        }

        return DB::transaction(function () use ($result): Certificate {
            $certificate = Certificate::query()->firstOrCreate(
                ['exam_result_id' => $result->id],
                [
                    'student_id' => $result->student_id,
                    'certificate_number' => sprintf('CBT-EDUKA-%s-%06d', now()->format('Y'), $result->id),
                    'status' => 'active',
                    'issued_at' => now(),
                    'metadata' => ['exam' => $result->exam->name, 'score' => (float) $result->score, 'student' => $result->student->name],
                ]
            );
            $this->auditLog->record(null, 'certificate.issued', $certificate, ['result_id' => $result->id]);

            return $certificate;
        });
    }

    public function revoke(User $actor, Certificate $certificate): Certificate
    {
        $certificate->load('result.exam');
        if (! $actor->isActive() || ! $actor->hasRole([Role::ADMIN, Role::TEACHER]) || (! $actor->hasRole(Role::ADMIN) && $certificate->result->exam->owner_id !== $actor->id)) {
            throw ValidationException::withMessages(['certificate' => 'Anda tidak memiliki akses mencabut sertifikat ini.']);
        }
        $certificate->update(['status' => 'revoked', 'revoked_at' => now()]);
        $this->auditLog->record($actor, 'certificate.revoked', $certificate);

        return $certificate->refresh();
    }

    public function verify(string $number): ?Certificate
    {
        return Certificate::query()->where('certificate_number', $number)->where('status', 'active')->with(['student', 'result.exam'])->first();
    }
}
