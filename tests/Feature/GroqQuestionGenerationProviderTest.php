<?php

namespace Tests\Feature;

use App\Enums\AiProviderStatus;
use App\Services\GroqQuestionGenerationProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GroqQuestionGenerationProviderTest extends TestCase
{
    public function test_disabled_and_unconfigured_statuses_are_distinct(): void
    {
        config(['cbt.ai.enabled' => false, 'cbt.ai.provider' => 'groq']);
        $provider = app(GroqQuestionGenerationProvider::class);

        $this->assertSame(AiProviderStatus::DISABLED, $provider->status());

        config(['cbt.ai.enabled' => true, 'cbt.ai.api_key' => null]);

        $this->assertSame(AiProviderStatus::NOT_CONFIGURED, $provider->status());
    }

    public function test_successful_response_is_parsed_and_usage_is_returned(): void
    {
        $this->configureProvider();
        Http::fake(['https://api.groq.com/*' => Http::response($this->response(), 200)]);

        $result = app(GroqQuestionGenerationProvider::class)->generate($this->parameters());

        $this->assertTrue($result->available);
        $this->assertSame(AiProviderStatus::READY, $result->status);
        $this->assertCount(1, $result->questions);
        $this->assertSame(30, $result->metadata['total_tokens']);
    }

    #[DataProvider('providerFailures')]
    public function test_provider_failures_are_mapped_without_exposing_raw_response(int $status, AiProviderStatus $expected, string $message): void
    {
        $this->configureProvider();
        Http::fake(['https://api.groq.com/*' => Http::response(['error' => ['message' => 'secret provider details']], $status)]);

        $result = app(GroqQuestionGenerationProvider::class)->generate($this->parameters());

        $this->assertFalse($result->available);
        $this->assertSame($expected, $result->status);
        $this->assertSame($message, $result->message);
        $this->assertStringNotContainsString('secret provider details', (string) $result->message);
    }

    public function test_invalid_json_does_not_become_a_successful_draft(): void
    {
        $this->configureProvider();
        Http::fake(['https://api.groq.com/*' => Http::response([
            'choices' => [['message' => ['content' => '{not-json']]],
        ], 200)]);

        $result = app(GroqQuestionGenerationProvider::class)->generate($this->parameters());

        $this->assertFalse($result->available);
        $this->assertSame(AiProviderStatus::PROVIDER_ERROR, $result->status);
        $this->assertEmpty($result->questions);
    }

    public function test_empty_completion_does_not_become_a_successful_draft(): void
    {
        $this->configureProvider();
        Http::fake(['https://api.groq.com/*' => Http::response([
            'choices' => [['message' => ['content' => '']]],
        ], 200)]);

        $result = app(GroqQuestionGenerationProvider::class)->generate($this->parameters());

        $this->assertFalse($result->available);
        $this->assertSame(AiProviderStatus::PROVIDER_ERROR, $result->status);
    }

    public function test_unexpected_json_shape_does_not_become_a_successful_draft(): void
    {
        $this->configureProvider();
        Http::fake(['https://api.groq.com/*' => Http::response([
            'choices' => [['message' => ['content' => '"unexpected"']]],
        ], 200)]);

        $result = app(GroqQuestionGenerationProvider::class)->generate($this->parameters());

        $this->assertFalse($result->available);
        $this->assertSame(AiProviderStatus::PROVIDER_ERROR, $result->status);
        $this->assertEmpty($result->questions);
    }

    public function test_schema_rejection_falls_back_once_to_json_object_and_validates_again(): void
    {
        $this->configureProvider();
        Http::fakeSequence()
            ->push(['error' => ['message' => 'response_format json schema is unsupported']], 400)
            ->push($this->response(), 200);

        $result = app(GroqQuestionGenerationProvider::class)->generate($this->parameters());

        $this->assertTrue($result->available);
        $this->assertSame('json_object', $result->metadata['response_format']);
        Http::assertSentCount(2);
    }

    public function test_connection_exception_is_reported_as_unavailable(): void
    {
        $this->configureProvider();
        Http::fake(fn () => throw new ConnectionException('network unavailable'));

        $result = app(GroqQuestionGenerationProvider::class)->generate($this->parameters());

        $this->assertSame(AiProviderStatus::UNAVAILABLE, $result->status);
        $this->assertFalse($result->available);
    }

    public static function providerFailures(): array
    {
        return [
            'authentication' => [401, AiProviderStatus::AUTHENTICATION_FAILED, 'API key AI tidak valid atau tidak memiliki akses.'],
            'rate limited' => [429, AiProviderStatus::RATE_LIMITED, 'Batas penggunaan AI tercapai. Tunggu sebentar lalu coba kembali.'],
            'server error' => [500, AiProviderStatus::PROVIDER_ERROR, 'Layanan AI menolak permintaan. Periksa model dan konfigurasi provider.'],
        ];
    }

    private function configureProvider(): void
    {
        config([
            'cbt.ai.enabled' => true,
            'cbt.ai.provider' => 'groq',
            'cbt.ai.api_key' => 'test-key',
            'cbt.ai.model' => 'openai/gpt-oss-20b',
            'cbt.ai.base_url' => 'https://api.groq.com/openai/v1',
        ]);
    }

    /** @return array<string, mixed> */
    private function parameters(): array
    {
        return [
            'subject' => 'Matematika',
            'topic' => 'Limit',
            'description' => '',
            'grade' => 'XII IPA',
            'difficulty' => 'medium',
            'count' => 1,
            'type' => 'multiple_choice',
            'language' => 'Bahasa Indonesia',
            'instructions' => '',
        ];
    }

    /** @return array<string, mixed> */
    private function response(): array
    {
        return [
            'choices' => [[
                'message' => ['content' => json_encode(['questions' => [[
                    'type' => 'multiple_choice',
                    'prompt' => 'Apa hasil dari 2 + 2?',
                    'options' => [
                        ['key' => 'A', 'label' => '3', 'is_correct' => false],
                        ['key' => 'B', 'label' => '4', 'is_correct' => true],
                    ],
                    'explanation' => 'Hasil penjumlahan adalah empat.',
                    'definition' => ['correct' => null, 'accepted_answers' => [], 'pairs' => [], 'correct_order' => [], 'blanks' => [], 'rubric' => '', 'partial_scoring' => false],
                    'points' => 1,
                    'difficulty' => 'medium',
                ]]], JSON_UNESCAPED_UNICODE)],
            ]],
            'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 20, 'total_tokens' => 30],
        ];
    }
}
