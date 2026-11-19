<?php

namespace App\QuestionTypes;

use App\DTOs\ScoreResult;

class MultipleChoiceHandler extends AbstractQuestionTypeHandler
{
    public function score(mixed $answer, array $definition, float $points): ScoreResult
    {
        $correct = strtoupper((string) ($definition['correct_option'] ?? ''));
        $selected = strtoupper((string) $answer);

        return $this->result($selected !== '' && $selected === $correct ? $points : 0.0, $points, $selected === $correct ? 'correct' : 'incorrect');
    }
}
