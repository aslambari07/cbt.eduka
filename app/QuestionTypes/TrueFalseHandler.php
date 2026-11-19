<?php

namespace App\QuestionTypes;

use App\DTOs\ScoreResult;

class TrueFalseHandler extends AbstractQuestionTypeHandler
{
    public function score(mixed $answer, array $definition, float $points): ScoreResult
    {
        $selected = filter_var($answer, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $correct = filter_var($definition['correct'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $isCorrect = $selected !== null && $correct !== null && $selected === $correct;

        return $this->result($isCorrect ? $points : 0.0, $points, $isCorrect ? 'correct' : 'incorrect');
    }
}
