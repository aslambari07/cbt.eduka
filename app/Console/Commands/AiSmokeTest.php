<?php

namespace App\Console\Commands;

use App\Contracts\QuestionGenerationProvider;
use App\Enums\AiProviderStatus;
use Illuminate\Console\Command;

class AiSmokeTest extends Command
{
    protected $signature = 'ai:smoke-test';

    protected $description = 'Check the configured AI provider and active model without generating or saving questions.';

    public function handle(QuestionGenerationProvider $provider): int
    {
        $connectionStatus = $provider->checkConnection();

        $this->line('AI provider: '.(string) config('cbt.ai.provider'));
        $this->line('AI model: '.(string) config('cbt.ai.model'));
        $this->line('Connection status: '.$connectionStatus->value);

        if ($connectionStatus !== AiProviderStatus::READY) {
            $this->warn('Live AI smoke test did not pass. No question was generated or saved.');

            return self::FAILURE;
        }

        $result = $provider->generate([
            'subject' => 'Matematika',
            'topic' => 'Penjumlahan bilangan bulat',
            'description' => 'Buat satu soal pengujian singkat untuk smoke test.',
            'grade' => 'XII IPA',
            'difficulty' => 'easy',
            'count' => 1,
            'type' => 'multiple_choice',
            'language' => 'Bahasa Indonesia',
            'instructions' => 'Kembalikan satu soal yang jelas dan singkat.',
        ]);

        $this->line('Generation status: '.$result->status->value);

        if ($result->available && count($result->questions) === 1) {
            $this->info('Live Groq completion and schema validation succeeded. No question was saved.');

            return self::SUCCESS;
        }

        $this->warn('Live AI generation did not produce a valid question. No question was saved.');

        return self::FAILURE;
    }
}
