<?php

namespace Tests\Unit;

use App\Services\AiQuestionValidator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AiQuestionValidatorTest extends TestCase
{
    #[DataProvider('supportedQuestionTypes')]
    public function test_all_supported_question_types_are_validated(string $type, array $definition, array $options): void
    {
        $question = [
            'type' => $type,
            'prompt' => $type === 'fill_blank' ? 'Lengkapi kalimat berikut: [BLANK_1].' : 'Jelaskan konsep utama materi ini dengan singkat.',
            'options' => $options,
            'explanation' => 'Penjelasan yang sesuai dengan jawaban.',
            'definition' => array_merge([
                'correct' => null,
                'accepted_answers' => [],
                'pairs' => [],
                'correct_order' => [],
                'blanks' => [],
                'rubric' => '',
                'partial_scoring' => false,
            ], $definition),
            'points' => 2,
            'difficulty' => 'medium',
        ];

        $result = app(AiQuestionValidator::class)->validate([
            'questions' => [$question],
        ], 1);

        $this->assertCount(1, $result['questions']);
        $this->assertSame($type, $result['questions'][0]['type']);
    }

    public function test_duplicate_prompts_are_rejected(): void
    {
        $question = $this->question('multiple_choice');

        $this->expectException(InvalidArgumentException::class);
        app(AiQuestionValidator::class)->validate(['questions' => [$question, $question]], 2);
    }

    public function test_multiple_choice_requires_exactly_one_correct_answer(): void
    {
        $question = $this->question('multiple_choice');
        $question['options'][0]['is_correct'] = false;

        $this->expectException(InvalidArgumentException::class);
        app(AiQuestionValidator::class)->validate(['questions' => [$question]], 1);
    }

    public static function supportedQuestionTypes(): array
    {
        return [
            'multiple choice' => ['multiple_choice', [], [['key' => 'A', 'label' => 'Satu', 'is_correct' => true], ['key' => 'B', 'label' => 'Dua', 'is_correct' => false]]],
            'multiple select' => ['multiple_select', [], [['key' => 'A', 'label' => 'Satu', 'is_correct' => true], ['key' => 'B', 'label' => 'Dua', 'is_correct' => true]]],
            'true false' => ['true_false', ['correct' => true], []],
            'short answer' => ['short_answer', ['accepted_answers' => ['evaporasi']], []],
            'essay' => ['essay', ['rubric' => 'Jawaban menjelaskan konsep dengan benar.'], []],
            'matching' => ['matching', ['pairs' => [['left' => 'A', 'right' => '1'], ['left' => 'B', 'right' => '2']]], []],
            'ordering' => ['ordering', ['correct_order' => ['Pertama', 'Kedua']], []],
            'fill blank' => ['fill_blank', ['blanks' => [['evaporasi']]], []],
        ];
    }

    /** @return array<string, mixed> */
    private function question(string $type): array
    {
        return [
            'type' => $type,
            'prompt' => 'Pertanyaan valid untuk pengujian.',
            'options' => [['key' => 'A', 'label' => 'Benar', 'is_correct' => true], ['key' => 'B', 'label' => 'Salah', 'is_correct' => false]],
            'explanation' => 'Penjelasan valid.',
            'definition' => ['correct' => null, 'accepted_answers' => [], 'pairs' => [], 'correct_order' => [], 'blanks' => [], 'rubric' => '', 'partial_scoring' => false],
            'points' => 1,
            'difficulty' => 'medium',
        ];
    }
}
