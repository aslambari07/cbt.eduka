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
use App\Services\ExamGradingService;
use App\Services\ExamSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamGradingTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_grading_creates_result_details_and_waits_for_essay_review(): void
    {
        [$exam, $teacher, $student, $questions] = $this->fixture();
        $sessions = app(ExamSessionService::class);
        $session = $sessions->start($student, $exam);
        $sessions->saveAnswer($session, $student, $questions['choice'], 'B');
        $sessions->saveAnswer($session, $student, $questions['short'], 'Evaporasi');
        $sessions->saveAnswer($session, $student, $questions['essay'], 'Urbanisasi meningkatkan kepadatan.');
        $sessions->submit($session, $student);

        $result = app(ExamGradingService::class)->grade($session->fresh());

        $this->assertSame('pending', $result->status->value);
        $this->assertSame(5.0, (float) $result->earned_points);
        $this->assertSame(50.0, (float) $result->score);
        $this->assertCount(3, $result->details);
        $this->assertDatabaseHas('exam_result_details', ['question_id' => $questions['choice']->id, 'grading_status' => 'auto_graded', 'earned_points' => 2]);
        $this->assertSame(ExamSessionStatus::COMPLETED, $session->fresh()->status);
    }

    public function test_teacher_can_manually_grade_essay_and_finalize_result(): void
    {
        [$exam, $teacher, $student, $questions] = $this->fixture();
        $sessions = app(ExamSessionService::class);
        $session = $sessions->start($student, $exam);
        $sessions->saveAnswer($session, $student, $questions['choice'], 'B');
        $sessions->saveAnswer($session, $student, $questions['short'], 'Evaporasi');
        $sessions->saveAnswer($session, $student, $questions['essay'], 'Jawaban esai.');
        $sessions->submit($session, $student);
        $result = app(ExamGradingService::class)->grade($session->fresh());
        $detail = $result->details()->where('question_id', $questions['essay']->id)->firstOrFail();

        $result = app(ExamGradingService::class)->gradeManual($teacher, $detail, 5, 'Argumen jelas.');

        $this->assertSame('graded', $result->status->value);
        $this->assertSame(100.0, (float) $result->score);
        $this->assertSame('Argumen jelas.', $detail->fresh()->feedback);
    }

    /** @return array{0: Exam, 1: User, 2: User, 3: array<string, Question>} */
    private function fixture(): array
    {
        $institution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $student = User::factory()->create(['role' => Role::STUDENT, 'institution_id' => $institution->id]);
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);
        $choice = Question::factory()->create(['author_id' => $teacher->id, 'subject_id' => $subject->id, 'type' => QuestionType::MULTIPLE_CHOICE, 'points' => 2, 'definition' => ['correct_option' => 'B']]);
        $choice->options()->createMany([
            ['key' => 'A', 'label' => 'Salah', 'is_correct' => false, 'sort_order' => 1],
            ['key' => 'B', 'label' => 'Benar', 'is_correct' => true, 'sort_order' => 2],
        ]);
        $short = Question::factory()->create(['author_id' => $teacher->id, 'subject_id' => $subject->id, 'type' => QuestionType::SHORT_ANSWER, 'points' => 3, 'definition' => ['accepted_answers' => ['evaporasi']]]);
        $essay = Question::factory()->create(['author_id' => $teacher->id, 'subject_id' => $subject->id, 'type' => QuestionType::ESSAY, 'points' => 5]);
        $exam = Exam::factory()->create(['owner_id' => $teacher->id, 'institution_id' => $institution->id, 'subject_id' => $subject->id, 'status' => ExamStatus::ONGOING, 'starts_at' => now()->subMinute(), 'ends_at' => now()->addHour(), 'duration_minutes' => 60, 'passing_grade' => 75]);
        $exam->questions()->attach([$choice->id => ['sort_order' => 1], $short->id => ['sort_order' => 2], $essay->id => ['sort_order' => 3]]);
        $exam->assignments()->create(['student_id' => $student->id, 'max_attempts' => 1, 'assigned_at' => now()]);

        return [$exam->fresh(), $teacher, $student, ['choice' => $choice->fresh('options'), 'short' => $short, 'essay' => $essay]];
    }
}
