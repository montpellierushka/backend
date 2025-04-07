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

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            Log::info('TelegramWebAppAuth middleware started', [
                'path' => $request->path(),
                'method' => $request->method()
            ]);

            // Получаем initData из заголовка
            $initData = $request->header('X-Telegram-Init-Data');
            
            if (!$initData) {
                Log::warning('No init data found in request');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Требуется аутентификация через Telegram Web App'
                ], 401);
            }

            // Проверяем подлинность initData
            if (!$this->telegramService->validateInitData($initData)) {
                Log::warning('Invalid init data');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Недействительные данные инициализации'
                ], 401);
            }

            // Получаем данные пользователя
            $userData = $this->telegramService->getUserData($initData);
            
            if (!$userData) {
                Log::warning('No user data found in init data');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Не удалось получить данные пользователя'
                ], 401);
            }

            // Добавляем данные пользователя в запрос
            $request->merge(['telegram_user' => $userData]);
            
            Log::info('TelegramWebAppAuth middleware completed successfully', [
                'user_id' => $userData['id'] ?? null
            ]);
            
            return $next($request);
        } catch (\Exception $e) {
            Log::error('Error in TelegramWebAppAuth middleware', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка аутентификации'
            ], 500);
        }
    }
} 