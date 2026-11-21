<?php

namespace App\DTOs;

use App\Enums\AiProviderStatus;

readonly class QuestionGenerationResult
{
    public function __construct(
        public bool $available,
        public array $questions = [],
        public ?string $message = null,
        public AiProviderStatus $status = AiProviderStatus::PROVIDER_ERROR,
        public array $metadata = [],
        public bool $partial = false,
    ) {}
}
