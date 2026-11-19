<?php

namespace App\DTOs;

final readonly class ScoreResult
{
    public function __construct(
        public float $score,
        public float $maxScore,
        public string $status,
        public ?string $feedback = null,
    ) {}
}
