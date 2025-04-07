<?php

namespace App\Providers;

use App\Services\TelegramWebAppService;
use Illuminate\Support\ServiceProvider;

class TelegramServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TelegramWebAppService::class, function ($app) {
            return new TelegramWebAppService(config('telegram.bot_token'));
        });
    }

    public function boot(): void
    {
        //
    }
} 