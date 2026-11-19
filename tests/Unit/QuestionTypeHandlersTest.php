<?php

namespace Tests\Unit;

use App\Enums\QuestionType;
use App\Services\QuestionTypeRegistry;
use Tests\TestCase;

class QuestionTypeHandlersTest extends TestCase
{
    public function test_multiple_choice_scores_only_the_correct_option(): void
    {
        $result = app(QuestionTypeRegistry::class)->for(QuestionType::MULTIPLE_CHOICE)->score('B', ['correct_option' => 'B'], 2.0);

        $this->assertSame(2.0, $result->score);
        $this->assertSame('correct', $result->status);
    }

    public function test_multiple_select_uses_explicit_partial_scoring(): void
    {
        $handler = app(QuestionTypeRegistry::class)->for(QuestionType::MULTIPLE_SELECT);
        $definition = ['correct_options' => ['A', 'C'], 'partial_scoring' => true];

        $partial = $handler->score(['A'], $definition, 4.0);
        $wrong = $handler->score(['A', 'B'], $definition, 4.0);

        $this->assertSame(2.0, $partial->score);
        $this->assertSame('partial', $partial->status);
        $this->assertSame(0.0, $wrong->score);
        $this->assertSame('incorrect', $wrong->status);
    }

    public function test_short_answer_normalizes_case_and_whitespace(): void
    {
        $result = app(QuestionTypeRegistry::class)->for(QuestionType::SHORT_ANSWER)->score('  Evaporasi  ', ['accepted_answers' => ['evaporasi']], 3.0);

        $this->assertSame(3.0, $result->score);
        $this->assertSame('correct', $result->status);
    }

    public function test_essay_requires_manual_grading(): void
    {
        $result = app(QuestionTypeRegistry::class)->for(QuestionType::ESSAY)->score('An argument.', [], 10.0);

        $this->assertSame(0.0, $result->score);
        $this->assertSame('pending', $result->status);
    }
}
