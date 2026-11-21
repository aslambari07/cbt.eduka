<?php

namespace App\Providers;

use App\Contracts\QuestionGenerationProvider;
use App\Services\GroqQuestionGenerationProvider;
use App\Services\UnavailableQuestionGenerationProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(QuestionGenerationProvider::class, function (Application $app): QuestionGenerationProvider {
            return config('cbt.ai.provider') === 'groq'
                ? $app->make(GroqQuestionGenerationProvider::class)
                : $app->make(UnavailableQuestionGenerationProvider::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request): Limit {
            return Limit::perMinute(3)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        RateLimiter::for('ai-generation', function (Request $request): Limit {
            return Limit::perMinute((int) config('cbt.ai.rate_limit', 10))
                ->by(($request->user()?->getAuthIdentifier() ?? 'guest').'|'.$request->ip());
        });
    }
}
