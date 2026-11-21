<?php

namespace App\Services;

use App\Contracts\QuestionGenerationProvider;
use App\DTOs\QuestionGenerationResult;
use App\Models\User;
use Illuminate\Http\Request;

class AiQuestionGeneratorService
{
    public function __construct(
        private readonly QuestionGenerationProvider $provider,
        private readonly AiUsageLogger $usageLogger,
    ) {}

    public function generate(User $user, array $parameters, ?Request $request = null): QuestionGenerationResult
    {
        $startedAt = microtime(true);
        $result = $this->provider->generate($parameters);

        $this->usageLogger->record($user, $parameters, $result, $startedAt, $request);

        return $result;
    }
}
