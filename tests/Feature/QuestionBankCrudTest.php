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

    public function test_published_multiple_choice_requires_exactly_one_correct_option(): void
    {
        $institution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);

        $response = $this->actingAs($teacher)->post(route('teacher.questions.store'), [
            'subject_id' => $subject->id,
            'type' => 'multiple_choice',
            'prompt' => 'Soal tidak valid',
            'points' => 2,
            'difficulty' => 'easy',
            'status' => 'published',
            'options' => [
                ['key' => 'A', 'label' => 'Satu', 'is_correct' => false],
                ['key' => 'B', 'label' => 'Dua', 'is_correct' => false],
            ],
        ]);

        $response->assertSessionHasErrors('options');
        $this->assertDatabaseMissing('questions', ['prompt' => 'Soal tidak valid']);
    }

    public function test_teacher_can_create_non_multiple_question_definitions(): void
    {
        $institution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);

        $this->actingAs($teacher)->post(route('teacher.questions.store'), [
            'subject_id' => $subject->id,
            'type' => 'matching',
            'prompt' => 'Pasangkan organ dan fungsinya.',
            'points' => 4,
            'difficulty' => 'medium',
            'status' => 'draft',
            'definition' => ['pairs' => [
                ['left' => 'Jantung', 'right' => 'Memompa darah'],
                ['left' => 'Paru-paru', 'right' => 'Pertukaran gas'],
            ]],
        ])->assertRedirect();

        $question = Question::query()->where('prompt', 'Pasangkan organ dan fungsinya.')->firstOrFail();
        $this->assertSame(['Jantung' => 'Memompa darah', 'Paru-paru' => 'Pertukaran gas'], $question->definition['pairs']);
    }

    public function test_global_admin_can_manage_questions_for_an_explicit_subject(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN, 'institution_id' => null]);
        $institution = Institution::factory()->create();
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);

        $this->actingAs($admin)->get(route('teacher.questions.create'))->assertOk()->assertSee($subject->name);
        $this->actingAs($admin)->post(route('teacher.questions.store'), [
            'subject_id' => $subject->id,
            'type' => 'short_answer',
            'prompt' => 'Sebutkan ibu kota Indonesia.',
            'points' => 1,
            'difficulty' => 'easy',
            'status' => 'draft',
            'definition' => ['accepted_answers' => ['Jakarta']],
        ])->assertRedirect();

        $this->assertDatabaseHas('questions', ['prompt' => 'Sebutkan ibu kota Indonesia.', 'author_id' => $admin->id]);
    }
}
