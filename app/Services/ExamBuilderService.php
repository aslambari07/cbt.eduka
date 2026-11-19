<?php

namespace App\Services;

use App\Enums\ExamAssignmentStatus;
use App\Enums\Role;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamBuilderService
{
    public function createDraft(User $owner, array $attributes, array $questionIds = [], array $studentIds = []): Exam
    {
        $this->ensureOwnerCanBuild($owner);
        $this->validateReferences($owner, $attributes, $questionIds, $studentIds);

        return DB::transaction(function () use ($owner, $attributes, $questionIds, $studentIds): Exam {
            $exam = Exam::create([
                'owner_id' => $owner->id,
                'institution_id' => $owner->institution_id,
                'subject_id' => $attributes['subject_id'],
                'class_id' => $attributes['class_id'] ?? null,
                'name' => $attributes['name'],
                'code' => $attributes['code'],
                'description' => $attributes['description'] ?? null,
                'instructions' => $attributes['instructions'] ?? null,
                'duration_minutes' => $attributes['duration_minutes'],
                'starts_at' => $attributes['starts_at'] ?? null,
                'ends_at' => $attributes['ends_at'] ?? null,
                'total_points' => $attributes['total_points'] ?? 0,
                'passing_grade' => $attributes['passing_grade'] ?? 75,
                'max_attempts' => $attributes['max_attempts'] ?? 1,
                'randomize_questions' => $attributes['randomize_questions'] ?? false,
                'randomize_options' => $attributes['randomize_options'] ?? false,
                'navigation_mode' => $attributes['navigation_mode'] ?? 'free',
                'results_visibility' => $attributes['results_visibility'] ?? 'after_review',
                'status' => 'draft',
            ]);

            $exam->questions()->attach(collect($questionIds)->values()->mapWithKeys(fn ($questionId, $index) => [
                $questionId => ['sort_order' => $index + 1, 'points' => null, 'required' => true],
            ])->all());

            if ($studentIds !== []) {
                $exam->assignments()->createMany(collect($studentIds)->unique()->map(fn ($studentId) => [
                    'student_id' => $studentId,
                    'status' => ExamAssignmentStatus::ASSIGNED,
                    'max_attempts' => $exam->max_attempts,
                    'assigned_at' => now(),
                ])->all());
            }

            return $exam->load(['questions', 'assignments']);
        });
    }

    public function updateDraft(User $owner, Exam $exam, array $attributes, array $questionIds = [], array $studentIds = []): Exam
    {
        if ($exam->status->value !== 'draft') {
            throw ValidationException::withMessages(['status' => 'Ujian yang sudah diterbitkan tidak dapat diubah dari builder.']);
        }

        if (! $owner->hasRole(Role::ADMIN) && ($exam->owner_id !== $owner->id || $exam->institution_id !== $owner->institution_id)) {
            throw ValidationException::withMessages(['exam' => 'Anda tidak memiliki akses ke ujian ini.']);
        }

        $this->validateReferences($owner, $attributes, $questionIds, $studentIds);

        return DB::transaction(function () use ($exam, $attributes, $questionIds, $studentIds): Exam {
            $exam->update([
                'subject_id' => $attributes['subject_id'],
                'class_id' => $attributes['class_id'] ?? null,
                'name' => $attributes['name'],
                'code' => $attributes['code'],
                'description' => $attributes['description'] ?? null,
                'instructions' => $attributes['instructions'] ?? null,
                'duration_minutes' => $attributes['duration_minutes'],
                'starts_at' => $attributes['starts_at'] ?? null,
                'ends_at' => $attributes['ends_at'] ?? null,
                'total_points' => $attributes['total_points'] ?? 0,
                'passing_grade' => $attributes['passing_grade'] ?? 75,
                'max_attempts' => $attributes['max_attempts'] ?? 1,
                'randomize_questions' => $attributes['randomize_questions'] ?? false,
                'randomize_options' => $attributes['randomize_options'] ?? false,
                'navigation_mode' => $attributes['navigation_mode'] ?? 'free',
                'results_visibility' => $attributes['results_visibility'] ?? 'after_review',
            ]);
            $exam->questions()->detach();
            $exam->questions()->attach(collect($questionIds)->values()->mapWithKeys(fn ($questionId, $index) => [
                $questionId => ['sort_order' => $index + 1, 'points' => null, 'required' => true],
            ])->all());
            $exam->assignments()->delete();
            if ($studentIds !== []) {
                $exam->assignments()->createMany(collect($studentIds)->unique()->map(fn ($studentId) => [
                    'student_id' => $studentId,
                    'status' => ExamAssignmentStatus::ASSIGNED,
                    'max_attempts' => $exam->max_attempts,
                    'assigned_at' => now(),
                ])->all());
            }

            return $exam->load(['questions', 'assignments']);
        });
    }

    private function ensureOwnerCanBuild(User $owner): void
    {
        if (! $owner->isActive() || ! $owner->hasRole([Role::ADMIN, Role::TEACHER]) || ! $owner->institution_id) {
            throw ValidationException::withMessages(['owner' => 'Pengguna tidak memiliki akses membuat ujian.']);
        }
    }

    private function validateReferences(User $owner, array $attributes, array $questionIds, array $studentIds): void
    {
        $subjectExists = $owner->institution->subjects()->whereKey($attributes['subject_id'] ?? 0)->exists();
        if (! $subjectExists) {
            throw ValidationException::withMessages(['subject_id' => 'Mata pelajaran tidak berada di institusi Anda.']);
        }

        if (! empty($attributes['class_id'])) {
            $classExists = $owner->institution->classes()->whereKey($attributes['class_id'])->exists();
            if (! $classExists) {
                throw ValidationException::withMessages(['class_id' => 'Kelas tidak berada di institusi Anda.']);
            }
        }

        $questions = app('db')->table('questions')->whereIn('id', $questionIds)->whereNull('deleted_at')->get(['id', 'author_id', 'subject_id']);
        if ($questions->count() !== count(array_unique($questionIds))) {
            throw ValidationException::withMessages(['question_ids' => 'Satu atau lebih soal tidak dapat digunakan.']);
        }

        if ($owner->hasRole(Role::TEACHER) && $questions->contains(fn ($question) => (int) $question->author_id !== $owner->id)) {
            throw ValidationException::withMessages(['question_ids' => 'Guru hanya dapat menggunakan soal miliknya.']);
        }

        if ($questions->contains(fn ($question) => (int) $question->subject_id !== (int) $attributes['subject_id'])) {
            throw ValidationException::withMessages(['question_ids' => 'Semua soal harus berasal dari mata pelajaran ujian.']);
        }

        if ($studentIds !== [] && ! empty($attributes['class_id'])) {
            $allowedStudents = app('db')->table('class_members')->where('class_id', $attributes['class_id'])->whereIn('student_id', $studentIds)->pluck('student_id');
            if ($allowedStudents->count() !== count(array_unique($studentIds))) {
                throw ValidationException::withMessages(['student_ids' => 'Satu atau lebih siswa bukan anggota kelas ujian.']);
            }
        }
    }
}
