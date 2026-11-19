<?php

namespace App\QuestionTypes;

use App\Contracts\QuestionTypeHandler;
use App\DTOs\ScoreResult;
use App\Models\Question;

abstract class AbstractQuestionTypeHandler implements QuestionTypeHandler
{
    public function validateDefinition(array $definition): array
    {
        return $definition;
    }

    public function normalizeAnswer(mixed $answer): mixed
    {
        return $answer;
    }

    public function renderData(Question $question, bool $includeKey = false): array
    {
        $question->loadMissing('options');

        $payload = [
            'id' => $question->id,
            'type' => $question->type->value,
            'prompt' => $question->prompt,
            'points' => (float) $question->points,
            'difficulty' => $question->difficulty,
            'options' => $question->relationLoaded('options') ? $question->options->map(fn ($option) => array_filter([
                'key' => $option->key,
                'label' => $option->label,
                'sort_order' => $option->sort_order,
                'is_correct' => $includeKey ? $option->is_correct : null,
            ], fn ($value) => $value !== null))->values()->all() : [],
        ];

        if ($includeKey) {
            $payload['definition'] = $question->definition ?? [];
        }

        return $payload;
    }

    protected function result(float $score, float $maxScore, string $status, ?string $feedback = null): ScoreResult
    {
        return new ScoreResult($score, $maxScore, $status, $feedback);
    }

    protected function normalizedString(mixed $value): string
    {
        return preg_replace('/\s+/', ' ', mb_strtolower(trim((string) $value))) ?? '';
    }
}
