<?php

namespace App\QuestionTypes;

use App\DTOs\ScoreResult;

class FillBlankHandler extends AbstractQuestionTypeHandler
{
    public function score(mixed $answer, array $definition, float $points): ScoreResult
    {
        $blanks = $definition['blanks'] ?? [];
        $submitted = is_array($answer) ? $answer : [$answer];
        $matches = collect($blanks)->filter(function ($accepted, $index) use ($submitted): bool {
            $answers = is_array($accepted) ? $accepted : [$accepted];
            $value = $this->normalizedString($submitted[$index] ?? '');

            return $value !== '' && collect($answers)->map(fn ($item) => $this->normalizedString($item))->contains($value);
        })->count();
        $score = $matches === count($blanks) ? $points : round($points * ($matches / max(1, count($blanks))), 2);

        return $this->result($score, $points, $score === $points ? 'correct' : ($score > 0 ? 'partial' : 'incorrect'));
    }
}
