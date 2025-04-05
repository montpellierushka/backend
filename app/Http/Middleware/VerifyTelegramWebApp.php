<?php

namespace App\Http\Middleware;

use App\Services\TelegramService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTelegramWebApp
{
    protected TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $initData = $request->header('X-Telegram-Init-Data');

        if (!$initData) {
            return response()->json([
                'ok' => false,
                'error' => 'Telegram init data is required',
            ], 401);
        }

        if (!$this->telegramService->validateWebAppData($initData)) {
            return response()->json([
                'ok' => false,
                'error' => 'Invalid Telegram init data',
            ], 401);
        }

        return $next($request);
    }
} 