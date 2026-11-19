<?php

namespace App\QuestionTypes;

use App\DTOs\ScoreResult;

class MatchingHandler extends AbstractQuestionTypeHandler
{
    public function score(mixed $answer, array $definition, float $points): ScoreResult
    {
        $pairs = $definition['pairs'] ?? [];
        $submitted = is_array($answer) ? $answer : [];
        $matches = collect($pairs)->filter(fn ($right, $left) => array_key_exists($left, $submitted) && (string) $submitted[$left] === (string) $right)->count();
        $score = $matches === count($pairs) ? $points : round($points * ($matches / max(1, count($pairs))), 2);

        return $this->result($score, $points, $score === $points ? 'correct' : ($score > 0 ? 'partial' : 'incorrect'));
    }
}
