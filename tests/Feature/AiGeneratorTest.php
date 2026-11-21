<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_sees_honest_disabled_ai_state_and_no_fake_questions(): void
    {
        config(['cbt.ai.enabled' => false, 'cbt.ai.provider' => 'groq']);
        $teacher = User::factory()->create(['role' => Role::TEACHER]);

        $this->actingAs($teacher)
            ->get(route('teacher.ai.index'))
            ->assertOk()
            ->assertSee('AI dinonaktifkan')
            ->assertSee('Belum ada draft soal')
            ->assertDontSee('Pertanyaan sintetis');

        $this->actingAs($teacher)
            ->post(route('teacher.ai.generate'), [
                'subject' => 'Matematika',
                'topic' => 'Limit',
                'difficulty' => 'medium',
                'count' => 5,
                'type' => 'multiple_choice',
                'language' => 'Bahasa Indonesia',
                'grade' => 'XII IPA',
            ])
            ->assertRedirect()
            ->assertSessionHas('ai_status_code', 'DISABLED');
    }

    public function test_student_cannot_access_ai_generator(): void
    {
        $student = User::factory()->create(['role' => Role::STUDENT]);

        $this->actingAs($student)->get(route('teacher.ai.index'))->assertForbidden();
    }

    public function test_teacher_can_generate_a_valid_draft_without_persisting_it(): void
    {
        config([
            'cbt.ai.enabled' => true,
            'cbt.ai.provider' => 'groq',
            'cbt.ai.api_key' => 'test-key',
            'cbt.ai.model' => 'openai/gpt-oss-20b',
        ]);

        $institution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);
        Http::fake(['https://api.groq.com/*' => Http::response($this->completionResponse(), 200)]);

        $response = $this->actingAs($teacher)->post(route('teacher.ai.generate'), [
            'subject_id' => $subject->id,
            'subject' => 'Matematika',
            'topic' => 'Limit',
            'difficulty' => 'medium',
            'count' => 1,
            'type' => 'multiple_choice',
            'language' => 'Bahasa Indonesia',
            'grade' => 'XII IPA',
        ]);

        $response->assertRedirect()->assertSessionHas('generated_questions');
        $this->assertDatabaseCount('questions', 0);
        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/chat/completions')
            && $request->hasHeader('Authorization', 'Bearer test-key')
            && $request->data()['model'] === 'openai/gpt-oss-20b');
    }

    /** @return array<string, mixed> */
    private function completionResponse(): array
    {
        return [
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'questions' => [[
                            'type' => 'multiple_choice',
                            'prompt' => 'Apa hasil dari 2 + 2?',
                            'options' => [
                                ['key' => 'A', 'label' => '3', 'is_correct' => false],
                                ['key' => 'B', 'label' => '4', 'is_correct' => true],
                            ],
                            'explanation' => 'Dua ditambah dua sama dengan empat.',
                            'definition' => ['correct' => null, 'accepted_answers' => [], 'pairs' => [], 'correct_order' => [], 'blanks' => [], 'rubric' => '', 'partial_scoring' => false],
                            'points' => 1,
                            'difficulty' => 'medium',
                        ]],
                    ], JSON_UNESCAPED_UNICODE),
                ],
            ]],
            'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 20, 'total_tokens' => 30],
        ];
    }
}
