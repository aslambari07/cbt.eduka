<?php

namespace Tests\Feature;

use App\Enums\ExamStatus;
use App\Enums\Role;
use App\Models\Exam;
use App\Models\Institution;
use App\Models\Question;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Services\ExamBuilderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ExamBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_a_draft_with_questions_and_student_assignments(): void
    {
        $institution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $student = User::factory()->create(['role' => Role::STUDENT, 'institution_id' => $institution->id]);
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);
        $class = SchoolClass::factory()->create(['institution_id' => $institution->id]);
        $class->students()->attach($student->id);
        $question = Question::factory()->create(['author_id' => $teacher->id, 'subject_id' => $subject->id]);

        $exam = app(ExamBuilderService::class)->createDraft($teacher, [
            'name' => 'Ujian Limit dan Turunan',
            'code' => 'EXAM-LIMIT-001',
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'duration_minutes' => 60,
            'passing_grade' => 75,
            'total_points' => 2,
        ], [$question->id], [$student->id]);

        $this->assertSame(ExamStatus::DRAFT, $exam->status);
        $this->assertCount(1, $exam->questions);
        $this->assertCount(1, $exam->assignments);
        $this->assertDatabaseHas('exam_assignments', ['exam_id' => $exam->id, 'student_id' => $student->id]);
    }

    public function test_teacher_can_create_an_exam_from_the_builder_form(): void
    {
        $institution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);
        $question = Question::factory()->create(['author_id' => $teacher->id, 'subject_id' => $subject->id]);

        $this->actingAs($teacher)->post(route('teacher.exams.store'), [
            'name' => 'Ujian Builder',
            'code' => 'EXAM-BUILDER-001',
            'subject_id' => $subject->id,
            'duration_minutes' => 45,
            'passing_grade' => 70,
            'total_points' => 1,
            'question_ids' => [$question->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('exams', ['code' => 'EXAM-BUILDER-001', 'owner_id' => $teacher->id]);
    }

    public function test_teacher_cannot_build_an_exam_with_a_question_from_another_institution(): void
    {
        $institution = Institution::factory()->create();
        $otherInstitution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);
        $foreignSubject = Subject::factory()->create(['institution_id' => $otherInstitution->id]);
        $question = Question::factory()->create(['subject_id' => $foreignSubject->id]);

        $this->expectException(ValidationException::class);

        app(ExamBuilderService::class)->createDraft($teacher, [
            'name' => 'Tidak boleh lintas institusi',
            'code' => 'EXAM-BLOCKED-001',
            'subject_id' => $subject->id,
            'duration_minutes' => 30,
            'passing_grade' => 75,
            'total_points' => 1,
        ], [$question->id]);

        $this->assertDatabaseMissing('exams', ['code' => 'EXAM-BLOCKED-001']);
    }

    public function test_teacher_cannot_update_another_teachers_exam(): void
    {
        $institution = Institution::factory()->create();
        $owner = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $intruder = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);
        $exam = Exam::factory()->create([
            'owner_id' => $owner->id,
            'institution_id' => $institution->id,
            'subject_id' => $subject->id,
        ]);

        $this->assertFalse($intruder->can('update', $exam));
    }
}
