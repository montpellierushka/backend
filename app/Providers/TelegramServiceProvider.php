<?php

namespace App\Providers;

use App\Services\TelegramWebAppService;
use Illuminate\Support\ServiceProvider;

class TelegramServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TelegramWebAppService::class, function ($app) {
            return new TelegramWebAppService();
        });

        $this->app->alias(TelegramWebAppService::class, 'telegram.webapp');
    }

    public function boot(): void
    {
        //
    }
} 