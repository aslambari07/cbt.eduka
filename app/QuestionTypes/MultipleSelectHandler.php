<?php

namespace App\QuestionTypes;

use App\DTOs\ScoreResult;

class MultipleSelectHandler extends AbstractQuestionTypeHandler
{
    public function score(mixed $answer, array $definition, float $points): ScoreResult
    {
        $correct = collect($definition['correct_options'] ?? [])->map(fn ($value) => strtoupper((string) $value))->unique()->sort()->values();
        $selected = collect(is_array($answer) ? $answer : [])->map(fn ($value) => strtoupper((string) $value))->unique()->sort()->values();

        if ($selected->diff($correct)->isNotEmpty() || $selected->isEmpty()) {
            return $this->result(0.0, $points, 'incorrect');
        }

        $matched = $selected->intersect($correct)->count();
        $score = $matched === $correct->count() ? $points : (($definition['partial_scoring'] ?? false) ? round($points * ($matched / max(1, $correct->count())), 2) : 0.0);

        return $this->result($score, $points, $score === $points ? 'correct' : ($score > 0 ? 'partial' : 'incorrect'));
    }
}
