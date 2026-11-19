<?php

namespace App\QuestionTypes;

use App\DTOs\ScoreResult;

class EssayHandler extends AbstractQuestionTypeHandler
{
    public function score(mixed $answer, array $definition, float $points): ScoreResult
    {
        return $this->result(0.0, $points, 'pending', 'Menunggu penilaian manual.');
    }
}
