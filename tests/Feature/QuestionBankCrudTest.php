<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Institution;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionBankCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_list_and_create_a_question(): void
    {
        $institution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);

        $this->actingAs($teacher)->get(route('teacher.questions.index'))
            ->assertOk()
            ->assertSee('Bank soal');

        $this->actingAs($teacher)->post(route('teacher.questions.store'), [
            'subject_id' => $subject->id,
            'type' => 'multiple_choice',
            'prompt' => 'Nilai dari 2 + 2 adalah?',
            'points' => 2,
            'difficulty' => 'easy',
            'status' => 'draft',
            'definition' => ['correct_option' => 'A'],
            'options' => [
                ['key' => 'A', 'label' => '3', 'is_correct' => false],
                ['key' => 'B', 'label' => '4', 'is_correct' => true],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('questions', ['prompt' => 'Nilai dari 2 + 2 adalah?', 'author_id' => $teacher->id]);
        $this->assertDatabaseHas('question_options', ['key' => 'B', 'is_correct' => 1]);
        $this->assertSame('B', Question::query()->firstOrFail()->definition['correct_option']);
    }
}
