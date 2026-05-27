<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\NoteRepositoryInterface;
use App\Repositories\NoteRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(NoteRepositoryInterface::class, NoteRepository::class);

        $this->app->singleton(\App\Services\AIServiceInterface::class, function ($app) {
            $provider = config('ai.provider', 'openai');
            if ($provider === 'gemini') {
                return $app->make(\App\Services\GeminiService::class);
            }
            return $app->make(\App\Services\OpenAIService::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
