<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiQuestionPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_drafts_are_saved_as_teacher_owned_drafts(): void
    {
        [$teacher, $subject] = $this->teacherAndSubject();

        $response = $this->actingAs($teacher)->post(route('teacher.ai.store'), [
            'questions' => [$this->draft($subject->id, 'Soal yang dipilih')],
        ]);

        $response->assertRedirect(route('teacher.ai.index'))->assertSessionHas('ai_status');
        $this->assertDatabaseHas('questions', [
            'author_id' => $teacher->id,
            'subject_id' => $subject->id,
            'prompt' => 'Soal yang dipilih',
            'status' => 'draft',
        ]);
        $this->assertDatabaseCount('questions', 1);
        $this->assertDatabaseCount('question_options', 2);
    }

    public function test_invalid_cross_institution_subject_rolls_back_batch(): void
    {
        [$teacher, $subject] = $this->teacherAndSubject();
        $otherInstitution = Institution::factory()->create();
        $otherSubject = Subject::factory()->create(['institution_id' => $otherInstitution->id]);

        $response = $this->actingAs($teacher)->post(route('teacher.ai.store'), [
            'questions' => [
                $this->draft($subject->id, 'Valid first draft'),
                $this->draft($otherSubject->id, 'Invalid second draft'),
            ],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('questions', 0);
    }

    /** @return array{0: User, 1: Subject} */
    private function teacherAndSubject(): array
    {
        $institution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);

        return [$teacher, $subject];
    }

    /** @return array<string, mixed> */
    private function draft(int $subjectId, string $prompt): array
    {
        return [
            'subject_id' => $subjectId,
            'type' => 'multiple_choice',
            'prompt' => $prompt,
            'options' => [
                ['key' => 'A', 'label' => 'Jawaban benar', 'is_correct' => true],
                ['key' => 'B', 'label' => 'Jawaban salah', 'is_correct' => false],
            ],
            'definition' => ['correct' => null, 'accepted_answers' => [], 'pairs' => [], 'correct_order' => [], 'blanks' => [], 'rubric' => '', 'partial_scoring' => false],
            'explanation' => 'Penjelasan soal.',
            'points' => 1,
            'difficulty' => 'medium',
        ];
    }
}
