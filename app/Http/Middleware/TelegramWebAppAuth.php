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
            $initData = $request->header('X-Telegram-Init-Data');

            if (!$initData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Отсутствуют данные инициализации Telegram'
                ], 401);
            }

            if (!$this->telegramService->validateInitData($initData)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Неверные данные инициализации Telegram'
                ], 401);
            }

            $userData = $this->telegramService->extractUserData($initData);

            if (!$userData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Не удалось извлечь данные пользователя'
                ], 401);
            }

            // Добавляем данные пользователя в запрос для дальнейшего использования
            $request->merge(['telegram_user' => $userData]);

            return $next($request);
        } catch (\Exception $e) {
            Log::error('Error in TelegramWebAppAuth middleware: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при проверке аутентификации'
            ], 500);
        }
    }
} 