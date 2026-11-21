<?php

namespace Tests\Feature;

use App\Enums\ExamResultStatus;
use App\Enums\ExamSessionStatus;
use App\Enums\ExamStatus;
use App\Enums\Role;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamSession;
use App\Models\Institution;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use App\Services\CertificateService;
use App\Services\ExamAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalyticsCertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_are_calculated_from_published_results(): void
    {
        [$exam, $student, $question] = $this->fixture();
        $session = $this->makeSession($exam, $student);
        $result = ExamResult::create(['exam_session_id' => $session->id, 'exam_id' => $exam->id, 'student_id' => $student->id, 'status' => ExamResultStatus::PUBLISHED, 'total_points' => 10, 'earned_points' => 8, 'score' => 80, 'passing_grade' => 75, 'is_passed' => true, 'published_at' => now()]);
        $result->details()->create(['question_id' => $question->id, 'max_points' => 10, 'earned_points' => 8, 'grading_status' => 'auto_graded']);

        $summary = app(ExamAnalyticsService::class)->summarize($exam->fresh());

        $this->assertSame(1, $summary['total_results']);
        $this->assertSame(80.0, $summary['average_score']);
        $this->assertSame(100.0, $summary['completion_rate']);
        $this->assertSame(80.0, $summary['question_accuracy'][0]['accuracy']);
    }

    public function test_certificate_is_idempotent_and_can_be_verified(): void
    {
        [$exam, $student] = $this->fixture();
        $session = $this->makeSession($exam, $student);
        $result = ExamResult::create(['exam_session_id' => $session->id, 'exam_id' => $exam->id, 'student_id' => $student->id, 'status' => ExamResultStatus::PUBLISHED, 'total_points' => 10, 'earned_points' => 9, 'score' => 90, 'passing_grade' => 75, 'is_passed' => true, 'published_at' => now()]);

        $service = app(CertificateService::class);
        $certificate = $service->issue($result);
        $same = $service->issue($result->fresh());

        $this->assertSame($certificate->id, $same->id);
        $this->assertSame($certificate->id, $service->verify($certificate->certificate_number)?->id);
        $this->actingAs($student)->get(route('student.certificates.download', $certificate))->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    /** @return array{0: Exam, 1: User, 2: Question} */
    private function fixture(): array
    {
        $institution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $student = User::factory()->create(['role' => Role::STUDENT, 'institution_id' => $institution->id]);
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);
        $question = Question::factory()->create(['author_id' => $teacher->id, 'subject_id' => $subject->id]);
        $exam = Exam::factory()->create(['owner_id' => $teacher->id, 'institution_id' => $institution->id, 'subject_id' => $subject->id, 'status' => ExamStatus::ONGOING]);
        $exam->questions()->attach($question->id, ['sort_order' => 1]);
        $exam->assignments()->create(['student_id' => $student->id, 'max_attempts' => 1, 'assigned_at' => now()]);

        return [$exam->fresh(), $student, $question];
    }

    private function makeSession(Exam $exam, User $student): ExamSession
    {
        $assignment = $exam->assignments()->where('student_id', $student->id)->firstOrFail();

        return ExamSession::create(['session_key' => (string) Str::uuid(), 'exam_id' => $exam->id, 'assignment_id' => $assignment->id, 'student_id' => $student->id, 'attempt_number' => 1, 'status' => ExamSessionStatus::COMPLETED, 'started_at' => now()->subHour(), 'expires_at' => now()->subMinute(), 'submitted_at' => now()->subMinute(), 'last_seen_at' => now(), 'question_order' => [$exam->questions()->first()->id], 'option_orders' => []]);
    }
}
