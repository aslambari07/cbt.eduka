<?php

namespace Tests\Feature;

use App\Enums\QuestionType;
use App\Enums\Role;
use App\Models\Institution;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class QuestionBankSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_render_data_does_not_expose_answer_keys(): void
    {
        $institution = Institution::factory()->create();
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
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

        $payload = $question->publicRenderData();

        $this->assertArrayNotHasKey('definition', $payload);
        $this->assertArrayNotHasKey('answer_key', $payload);
        $this->assertArrayNotHasKey('is_correct', $payload['options'][0]);
        $this->assertSame('Benar', $payload['options'][1]['label']);
    }

    public function test_teacher_cannot_update_another_teachers_question(): void
    {
        $institution = Institution::factory()->create();
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);
        $owner = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $other = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $question = Question::factory()->create(['author_id' => $owner->id, 'subject_id' => $subject->id]);

        $this->assertTrue(Gate::forUser($owner)->allows('update', $question));
        $this->assertFalse(Gate::forUser($other)->allows('update', $question));
    }
}
