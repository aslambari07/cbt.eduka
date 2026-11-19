<?php

namespace App\QuestionTypes;

use App\DTOs\ScoreResult;

class ShortAnswerHandler extends AbstractQuestionTypeHandler
{
    public function normalizeAnswer(mixed $answer): string
    {
        return $this->normalizedString($answer);
    }

    public function score(mixed $answer, array $definition, float $points): ScoreResult
    {
        $selected = $this->normalizeAnswer($answer);
        $accepted = collect($definition['accepted_answers'] ?? [])->map(fn ($value) => $this->normalizeAnswer($value));
        $isCorrect = $selected !== '' && $accepted->contains($selected);

        return $this->result($isCorrect ? $points : 0.0, $points, $isCorrect ? 'correct' : 'incorrect');
    }
}
