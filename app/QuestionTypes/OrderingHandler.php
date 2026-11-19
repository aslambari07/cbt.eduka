<?php

namespace App\QuestionTypes;

use App\DTOs\ScoreResult;

class OrderingHandler extends AbstractQuestionTypeHandler
{
    public function score(mixed $answer, array $definition, float $points): ScoreResult
    {
        $correct = array_values($definition['correct_order'] ?? []);
        $submitted = is_array($answer) ? array_values($answer) : [];
        $matches = collect($correct)->filter(fn ($value, $index) => ($submitted[$index] ?? null) === $value)->count();
        $score = $matches === count($correct) ? $points : round($points * ($matches / max(1, count($correct))), 2);

        return $this->result($score, $points, $score === $points ? 'correct' : ($score > 0 ? 'partial' : 'incorrect'));
    }
}
