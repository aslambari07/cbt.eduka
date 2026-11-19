<?php

namespace App\Services;

use App\Enums\ExamStatus;
use App\Models\Exam;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class ExamLifecycleService
{
    /** @var array<string, list<ExamStatus>> */
    private array $allowed = [
        'draft' => [ExamStatus::SCHEDULED, ExamStatus::PUBLISHED, ExamStatus::ARCHIVED],
        'scheduled' => [ExamStatus::PUBLISHED, ExamStatus::ONGOING, ExamStatus::ARCHIVED],
        'published' => [ExamStatus::ONGOING, ExamStatus::ARCHIVED],
        'ongoing' => [ExamStatus::COMPLETED, ExamStatus::ARCHIVED],
        'completed' => [ExamStatus::ARCHIVED],
        'archived' => [],
    ];

    public function transition(Exam $exam, ExamStatus $target, ?CarbonImmutable $now = null): Exam
    {
        $current = $exam->status;
        $now ??= CarbonImmutable::now();

        if (! in_array($target, $this->allowed[$current->value] ?? [], true)) {
            throw ValidationException::withMessages(['status' => "Transisi {$current->value} ke {$target->value} tidak diizinkan."]);
        }

        if (in_array($target, [ExamStatus::SCHEDULED, ExamStatus::PUBLISHED], true)) {
            $this->ensurePublishable($exam, $target);
        }

        if ($target === ExamStatus::ONGOING && $exam->starts_at && $now->lessThan($exam->starts_at)) {
            throw ValidationException::withMessages(['status' => 'Ujian belum memasuki waktu mulai.']);
        }

        if ($target === ExamStatus::COMPLETED && $exam->ends_at && $now->lessThan($exam->ends_at)) {
            throw ValidationException::withMessages(['status' => 'Ujian belum melewati waktu selesai.']);
        }

        $exam->forceFill(['status' => $target])->save();

        return $exam->refresh();
    }

    private function ensurePublishable(Exam $exam, ExamStatus $target): void
    {
        $errors = [];

        if (! $exam->subject_id) {
            $errors['subject_id'] = 'Mata pelajaran wajib dipilih.';
        }

        if (! $exam->questions()->exists()) {
            $errors['questions'] = 'Tambahkan minimal satu soal sebelum menerbitkan ujian.';
        }

        if ($target === ExamStatus::SCHEDULED && ! $exam->starts_at) {
            $errors['starts_at'] = 'Jadwal mulai wajib diisi untuk ujian terjadwal.';
        }

        if ($exam->starts_at && $exam->ends_at && $exam->ends_at->lessThanOrEqualTo($exam->starts_at)) {
            $errors['ends_at'] = 'Waktu selesai harus setelah waktu mulai.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
