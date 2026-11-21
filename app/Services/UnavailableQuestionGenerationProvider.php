<?php

namespace App\Services;

use App\Contracts\QuestionGenerationProvider;
use App\DTOs\QuestionGenerationResult;
use App\Enums\AiProviderStatus;

class UnavailableQuestionGenerationProvider implements QuestionGenerationProvider
{
    public function status(): AiProviderStatus
    {
        return config('cbt.ai.enabled') === false
            ? AiProviderStatus::DISABLED
            : AiProviderStatus::NOT_CONFIGURED;
    }

    public function checkConnection(): AiProviderStatus
    {
        return $this->status();
    }

    public function generate(array $parameters): QuestionGenerationResult
    {
        $status = $this->status();
        $message = $status === AiProviderStatus::DISABLED
            ? 'Fitur AI sedang dinonaktifkan oleh konfigurasi aplikasi.'
            : 'AI belum dikonfigurasi. Tambahkan AI_PROVIDER dan AI_API_KEY di environment server.';

        return new QuestionGenerationResult(false, [], $message, $status);
    }
}
