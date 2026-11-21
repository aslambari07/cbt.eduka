<?php

namespace App\Services;

use App\DTOs\QuestionGenerationResult;
use App\Models\User;
use Illuminate\Http\Request;

class AiUsageLogger
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function record(User $user, array $parameters, QuestionGenerationResult $result, float $startedAt, ?Request $request = null): void
    {
        $metadata = $result->metadata;
        $metadata['duration_ms'] ??= (int) round((microtime(true) - $startedAt) * 1000);

        $this->auditLogService->record($user, 'ai.question_generation', null, [
            'provider' => $metadata['provider'] ?? config('cbt.ai.provider'),
            'model' => $metadata['model'] ?? config('cbt.ai.model'),
            'status' => $result->status->value,
            'success' => $result->available,
            'requested_count' => (int) ($parameters['count'] ?? 0),
            'returned_count' => count($result->questions),
            'duration_ms' => $metadata['duration_ms'],
            'input_tokens' => $metadata['input_tokens'] ?? null,
            'output_tokens' => $metadata['output_tokens'] ?? null,
            'total_tokens' => $metadata['total_tokens'] ?? null,
            'error_category' => $result->available ? null : $result->status->value,
        ], $request);
    }
}
