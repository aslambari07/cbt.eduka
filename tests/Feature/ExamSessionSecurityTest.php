<?php

namespace Tests\Feature;

use App\Enums\ExamSessionStatus;
use App\Enums\ExamStatus;
use App\Enums\QuestionType;
use App\Enums\Role;
use App\Models\Exam;
use App\Models\Institution;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use App\Services\ExamSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ExamSessionSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_session_payload_is_key_safe_and_restores_saved_answer(): void
    {
        [$exam, $student, $question] = $this->examFixture();
        $service = app(ExamSessionService::class);

        $session = $service->start($student, $exam);
        $payload = $service->payload($session, $student);

        $this->assertSame(ExamSessionStatus::IN_PROGRESS, $session->status);
        $this->assertArrayNotHasKey('definition', $payload['questions'][0]);
        $this->assertArrayNotHasKey('is_correct', $payload['questions'][0]['options'][0]);

        $saved = $service->saveAnswer($session, $student, $question, 'B', 0);
        $restored = $service->payload($session->fresh(), $student);

        $this->assertSame('B', $saved->answer);
        $this->assertSame(1, $saved->version);
        $this->assertSame('B', $restored['questions'][0]['answer']);
        $this->assertSame(ExamSessionStatus::SAVED, $session->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'exam.answer_saved', 'actor_id' => $student->id]);
    }

    public function test_stale_answer_request_cannot_overwrite_newer_answer(): void
    {
        [$exam, $student, $question] = $this->examFixture();
        $service = app(ExamSessionService::class);
        $session = $service->start($student, $exam);

        $service->saveAnswer($session, $student, $question, 'B', 0);
        $stale = $service->saveAnswer($session->fresh(), $student, $question, 'A', 0);

        $this->assertSame('B', $stale->answer);
        $this->assertSame(1, $stale->version);
    }

    public function test_foreign_student_cannot_read_or_write_another_students_session(): void
    {
        [$exam, $student, $question] = $this->examFixture();
        $foreignStudent = User::factory()->create(['role' => Role::STUDENT, 'institution_id' => $student->institution_id]);
        $service = app(ExamSessionService::class);
        $session = $service->start($student, $exam);

        $this->expectException(ValidationException::class);
        $service->payload($session, $foreignStudent);
    }

    public function test_submission_is_idempotent_and_locks_future_answer_changes(): void
    {
        [$exam, $student, $question] = $this->examFixture();
        $service = app(ExamSessionService::class);
        $session = $service->start($student, $exam);
        $service->saveAnswer($session, $student, $question, 'B', 0);

        $submitted = $service->submit($session->fresh(), $student);
        $again = $service->submit($submitted, $student);

        $this->assertSame(ExamSessionStatus::SUBMITTED, $again->status);
        $this->assertNotNull($again->submitted_at);
        $this->expectException(ValidationException::class);
        $service->saveAnswer($again, $student, $question, 'A', 1);
    }

    public function test_expired_server_timer_rejects_answer_and_marks_session_expired(): void
    {
        [$exam, $student, $question] = $this->examFixture();
        $service = app(ExamSessionService::class);
        $session = $service->start($student, $exam);
        $session->forceFill(['expires_at' => now()->subSecond()])->save();

        $this->expectException(ValidationException::class);
        $service->saveAnswer($session->fresh(), $student, $question, 'B', 0);

        $this->assertSame(ExamSessionStatus::TIME_EXPIRED, $session->fresh()->status);
    }

    /** @return array{0: Exam, 1: User, 2: Question} */
    private function examFixture(): array
    {
        $institution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $student = User::factory()->create(['role' => Role::STUDENT, 'institution_id' => $institution->id]);
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);
        $question = Question::factory()->create([
            'author_id' => $teacher->id,
            'subject_id' => $subject->id,
            'type' => QuestionType::MULTIPLE_CHOICE,
            'definition' => ['correct_option' => 'B'],
        ]);
        $question->options()->createMany([
            ['key' => 'A', 'label' => 'Salah', 'is_correct' => false, 'sort_order' => 1],
            ['key' => 'B', 'label' => 'Benar', 'is_correct' => true, 'sort_order' => 2],
        ]);
        $exam = Exam::factory()->create([
            'owner_id' => $teacher->id,
            'institution_id' => $institution->id,
            'subject_id' => $subject->id,
            'status' => ExamStatus::ONGOING,
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addHour(),
            'duration_minutes' => 60,
            'max_attempts' => 1,
        ]);
        $exam->questions()->attach($question->id, ['sort_order' => 1, 'required' => true]);
        $exam->assignments()->create(['student_id' => $student->id, 'max_attempts' => 1, 'assigned_at' => now()]);

        return [$exam->fresh(), $student, $question->fresh('options')];
    }
}
