<?php

namespace Tests\Unit;

use App\Enums\ExamStatus;
use App\Enums\Role;
use App\Models\Exam;
use App\Models\Institution;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use App\Services\ExamLifecycleService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ExamLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_cannot_be_published_without_questions(): void
    {
        $institution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);
        $exam = Exam::factory()->create([
            'owner_id' => $teacher->id,
            'institution_id' => $institution->id,
            'subject_id' => $subject->id,
            'status' => ExamStatus::DRAFT,
        ]);

        $this->expectException(ValidationException::class);

        app(ExamLifecycleService::class)->transition($exam, ExamStatus::PUBLISHED);
    }

    public function test_published_exam_can_start_inside_its_configured_window(): void
    {
        $institution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);
        $exam = Exam::factory()->create([
            'owner_id' => $teacher->id,
            'institution_id' => $institution->id,
            'subject_id' => $subject->id,
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addHour(),
            'status' => ExamStatus::DRAFT,
        ]);
        $question = Question::factory()->create(['author_id' => $teacher->id, 'subject_id' => $subject->id]);
        $exam->questions()->attach($question->id, ['sort_order' => 1]);

        $lifecycle = app(ExamLifecycleService::class);
        $lifecycle->transition($exam, ExamStatus::PUBLISHED);
        $lifecycle->transition($exam->fresh(), ExamStatus::ONGOING, CarbonImmutable::now());

        $this->assertSame(ExamStatus::ONGOING, $exam->fresh()->status);
    }

    public function test_exam_cannot_move_back_to_draft_after_publication(): void
    {
        $institution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);
        $exam = Exam::factory()->create([
            'owner_id' => $teacher->id,
            'institution_id' => $institution->id,
            'subject_id' => $subject->id,
            'status' => ExamStatus::PUBLISHED,
        ]);

        $this->expectException(ValidationException::class);

        app(ExamLifecycleService::class)->transition($exam, ExamStatus::DRAFT);
    }
}
