<?php

namespace Tests\Feature;

use App\Enums\ExamResultStatus;
use App\Enums\ExamSessionStatus;
use App\Enums\ExamStatus;
use App\Enums\QuestionType;
use App\Enums\Role;
use App\Models\Exam;
use App\Models\ExamAssignment;
use App\Models\ExamResult;
use App\Models\ExamSession;
use App\Models\Institution;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use App\Services\ExamGradingService;
use App\Services\ExamPublicationService;
use App\Services\ExamSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResultPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_result_is_hidden_until_teacher_publishes_it(): void
    {
        [$exam, $teacher, $student, $question] = $this->fixture();
        $sessionService = app(ExamSessionService::class);
        $session = $sessionService->start($student, $exam);
        $sessionService->saveAnswer($session, $student, $question, 'B');
        $sessionService->submit($session, $student);
        $result = app(ExamGradingService::class)->grade($session->fresh());

        $this->assertFalse($student->can('view', $result));
        app(ExamPublicationService::class)->publish($teacher, $result);

        $this->assertTrue($student->fresh()->can('view', $result->fresh()));
        $this->assertSame('published', $result->fresh()->status->value);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $student->id, 'type' => 'App\\Notifications\\ResultPublishedNotification']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'result.published', 'actor_id' => $teacher->id]);
    }

    public function test_teacher_can_export_exam_results_as_csv(): void
    {
        $teacher = User::factory()->create(['role' => Role::TEACHER]);
        $student = User::factory()->create(['role' => Role::STUDENT]);
        $exam = Exam::factory()->create(['owner_id' => $teacher->id]);
        $teacher->update(['institution_id' => $exam->institution_id]);
        $assignment = ExamAssignment::create(['exam_id' => $exam->id, 'student_id' => $student->id, 'status' => 'completed', 'max_attempts' => 1, 'assigned_at' => now()->subHour()]);
        $session = ExamSession::create(['session_key' => (string) Str::uuid(), 'exam_id' => $exam->id, 'assignment_id' => $assignment->id, 'student_id' => $student->id, 'attempt_number' => 1, 'status' => ExamSessionStatus::COMPLETED, 'started_at' => now()->subHour(), 'expires_at' => now()->subMinutes(30), 'submitted_at' => now()->subMinutes(30), 'last_seen_at' => now()->subMinutes(30), 'question_order' => [], 'option_orders' => []]);
        ExamResult::create(['exam_session_id' => $session->id, 'exam_id' => $exam->id, 'student_id' => $student->id, 'status' => ExamResultStatus::GRADED, 'total_points' => 10, 'earned_points' => 8, 'score' => 80, 'passing_grade' => 75, 'is_passed' => true, 'graded_at' => now()]);

        $response = $this->actingAs($teacher)->get(route('teacher.exams.results.export', $exam));
        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString($student->email, $response->streamedContent());
    }

    public function test_student_result_index_does_not_leak_after_review_scores(): void
    {
        [$exam, $teacher, $student] = array_slice($this->fixture(), 0, 3);
        $session = app(ExamSessionService::class)->start($student, $exam);
        app(ExamSessionService::class)->submit($session, $student);
        app(ExamGradingService::class)->grade($session->fresh());

        $this->actingAs($student)->get(route('student.results.index'))->assertOk()->assertDontSee($exam->name);
    }

    /** @return array{0: Exam, 1: User, 2: User, 3: Question} */
    private function fixture(): array
    {
        $institution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $student = User::factory()->create(['role' => Role::STUDENT, 'institution_id' => $institution->id]);
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);
        $question = Question::factory()->create(['author_id' => $teacher->id, 'subject_id' => $subject->id, 'type' => QuestionType::MULTIPLE_CHOICE, 'points' => 2, 'definition' => ['correct_option' => 'B']]);
        $question->options()->createMany([
            ['key' => 'A', 'label' => 'Salah', 'is_correct' => false, 'sort_order' => 1],
            ['key' => 'B', 'label' => 'Benar', 'is_correct' => true, 'sort_order' => 2],
        ]);
        $exam = Exam::factory()->create(['owner_id' => $teacher->id, 'institution_id' => $institution->id, 'subject_id' => $subject->id, 'status' => ExamStatus::ONGOING, 'starts_at' => now()->subMinute(), 'ends_at' => now()->addHour()]);
        $exam->questions()->attach($question->id, ['sort_order' => 1]);
        $exam->assignments()->create(['student_id' => $student->id, 'max_attempts' => 1, 'assigned_at' => now()]);

        return [$exam->fresh(), $teacher, $student, $question->fresh('options')];
    }
}
