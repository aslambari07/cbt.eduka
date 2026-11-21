<?php

namespace Tests\Feature;

use App\Enums\ExamResultStatus;
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
use Tests\TestCase;

class ExamSessionHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_enter_start_and_autosave_exam_over_http(): void
    {
        [$exam, $student, $question] = $this->fixture();

        $this->actingAs($student)->get(route('student.exams.show', $exam))->assertOk()->assertSee('Mulai ujian');
        $this->actingAs($student)->post(route('student.exams.start', $exam))->assertRedirect();
        $session = app(ExamSessionService::class)->start($student, $exam);

        $this->actingAs($student)->get(route('student.sessions.show', $session))->assertOk()->assertDontSee('correct_option')->assertDontSee('is_correct');
        $this->actingAs($student)->postJson(route('student.sessions.answers', [$session, $question]), ['answer' => 'B', 'client_version' => 0])->assertOk()->assertJsonPath('status', 'saved')->assertJsonPath('answer', 'B');
    }

    public function test_submission_automatically_creates_a_result(): void
    {
        [$exam, $student, $question] = $this->fixture();
        $session = app(ExamSessionService::class)->start($student, $exam);
        $this->actingAs($student)->postJson(route('student.sessions.answers', [$session, $question]), ['answer' => 'B', 'client_version' => 0])->assertOk();

        $this->actingAs($student)->post(route('student.sessions.submit', $session))->assertRedirect();

        $this->assertDatabaseHas('exam_results', ['exam_session_id' => $session->id, 'status' => 'graded', 'score' => 100]);
        $this->assertDatabaseHas('exam_sessions', ['id' => $session->id, 'status' => 'completed']);
    }

    public function test_immediate_result_visibility_publishes_after_submission(): void
    {
        [$exam, $student, $question] = $this->fixture();
        $exam->update(['results_visibility' => 'immediate']);
        $session = app(ExamSessionService::class)->start($student, $exam);
        $this->actingAs($student)->postJson(route('student.sessions.answers', [$session, $question]), ['answer' => 'B', 'client_version' => 0])->assertOk();

        $this->actingAs($student)->post(route('student.sessions.submit', $session))->assertRedirect();

        $this->assertDatabaseHas('exam_results', ['exam_session_id' => $session->id, 'status' => ExamResultStatus::PUBLISHED->value]);
        $this->actingAs($student)->get(route('student.results.index'))->assertSee($exam->name);
    }

    /** @return array{0: Exam, 1: User, 2: Question} */
    private function fixture(): array
    {
        $institution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $student = User::factory()->create(['role' => Role::STUDENT, 'institution_id' => $institution->id]);
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);
        $question = Question::factory()->create(['author_id' => $teacher->id, 'subject_id' => $subject->id, 'type' => QuestionType::MULTIPLE_CHOICE, 'definition' => ['correct_option' => 'B']]);
        $question->options()->createMany([
            ['key' => 'A', 'label' => 'Salah', 'is_correct' => false, 'sort_order' => 1],
            ['key' => 'B', 'label' => 'Benar', 'is_correct' => true, 'sort_order' => 2],
        ]);
        $exam = Exam::factory()->create(['owner_id' => $teacher->id, 'institution_id' => $institution->id, 'subject_id' => $subject->id, 'status' => ExamStatus::ONGOING, 'starts_at' => now()->subMinute(), 'ends_at' => now()->addHour()]);
        $exam->questions()->attach($question->id, ['sort_order' => 1]);
        $exam->assignments()->create(['student_id' => $student->id, 'max_attempts' => 1, 'assigned_at' => now()]);

        return [$exam->fresh(), $student, $question->fresh('options')];
    }
}
