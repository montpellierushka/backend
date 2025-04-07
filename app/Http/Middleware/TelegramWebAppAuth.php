<?php

namespace App\Http\Middleware;

use App\Services\TelegramWebAppService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TelegramWebAppAuth
{
    private TelegramWebAppService $telegramService;

    public function __construct(TelegramWebAppService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            \Log::info('TelegramWebAppAuth middleware started', [
                'headers' => $request->headers->all(),
                'path' => $request->path(),
                'method' => $request->method()
            ]);

            $initData = $request->header('X-Telegram-Init-Data');
            
            if (!$initData) {
                \Log::warning('No init data found in request');
                return response()->json(['message' => 'No init data provided'], 401);
            }

            \Log::info('Init data found', ['initData' => $initData]);

            if (!$this->telegramService->validateInitData($initData)) {
                \Log::warning('Invalid init data', ['initData' => $initData]);
                return response()->json(['message' => 'Invalid init data'], 401);
            }

            \Log::info('Init data validated successfully');

            $userData = $this->telegramService->extractUserData($initData);
            
            if (!$userData) {
                \Log::warning('No user data found in init data');
                return response()->json(['message' => 'No user data found'], 401);
            }

            \Log::info('User data extracted', ['userData' => $userData]);

            // Добавляем данные пользователя в запрос
            $request->merge(['telegram_user' => $userData]);
            
            \Log::info('TelegramWebAppAuth middleware completed successfully');
            
            return $next($request);
        } catch (\Exception $e) {
            \Log::error('Error in TelegramWebAppAuth middleware', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Authentication error'], 500);
        }
    }
} 