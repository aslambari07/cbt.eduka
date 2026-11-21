<?php

namespace App\Contracts;

use App\DTOs\QuestionGenerationResult;
use App\Enums\AiProviderStatus;

interface QuestionGenerationProvider
{
    public function status(): AiProviderStatus;

    public function checkConnection(): AiProviderStatus;

    public function generate(array $parameters): QuestionGenerationResult;
}
