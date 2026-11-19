<?php

namespace App\Contracts;

use App\DTOs\ScoreResult;
use App\Models\Question;

interface QuestionTypeHandler
{
    public function validateDefinition(array $definition): array;

    public function normalizeAnswer(mixed $answer): mixed;

    public function score(mixed $answer, array $definition, float $points): ScoreResult;

    public function renderData(Question $question, bool $includeKey = false): array;
}
