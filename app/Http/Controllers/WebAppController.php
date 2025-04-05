<?php

namespace App\Http\Controllers;

use App\Models\TelegramUser;
use App\Models\Message;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebAppController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Проверка инициализационных данных веб-приложения
     */
    public function validateInitData(Request $request)
    {
        try {
            $initData = $request->header('X-Telegram-Init-Data');
            
            if (!$initData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Init data is missing'
                ], 400);
            }

            $isValid = $this->telegramService->validateInitData($initData);
            
            return response()->json([
                'success' => $isValid,
                'message' => $isValid ? 'Init data is valid' : 'Init data is invalid'
            ]);
        } catch (\Exception $e) {
            Log::error('Error validating init data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Получение информации о пользователе
     */
    public function getUserInfo(Request $request)
    {
        try {
            $initData = $request->header('X-Telegram-Init-Data');
            $userData = $this->telegramService->getUserFromInitData($initData);
            
            if (!$userData) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $user = TelegramUser::firstOrCreate(
                ['telegram_id' => $userData['id']],
                [
                    'username' => $userData['username'] ?? null,
                    'first_name' => $userData['first_name'] ?? null,
                    'last_name' => $userData['last_name'] ?? null,
                    'language_code' => $userData['language_code'] ?? null,
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting user info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Получение истории сообщений пользователя
     */
    public function getMessages(Request $request)
    {
        try {
            $initData = $request->header('X-Telegram-Init-Data');
            $userData = $this->telegramService->getUserFromInitData($initData);
            
            if (!$userData) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $user = TelegramUser::where('telegram_id', $userData['id'])->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $messages = Message::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->take(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $messages
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
} 