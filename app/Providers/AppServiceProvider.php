<?php

namespace App\Providers;

use App\Services\Ai\AiProvider;
use App\Services\Ai\FakeProvider;
use App\Services\Ai\OpenAiCompatibleProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AiProvider::class, function () {
            if (config('services.ai.fake') || app()->environment('testing')) {
                return new FakeProvider();
            }

            return new OpenAiCompatibleProvider(
                endpoint: (string) config('services.ai.endpoint'),
                apiKey: (string) config('services.ai.key'),
                model: (string) config('services.ai.model'),
                timeout: (int) config('services.ai.timeout', 15),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
