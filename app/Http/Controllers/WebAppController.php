<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TelegramWebAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WebAppController extends Controller
{
    private TelegramWebAppService $telegramService;

    public function __construct(TelegramWebAppService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function validateInitData(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'initData' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ошибка валидации',
                    'errors' => $validator->errors()
                ], 422);
            }

            $isValid = $this->telegramService->validateInitData($request->initData);
            
            if (!$isValid) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Неверные данные инициализации'
                ], 401);
            }

            $userData = $this->telegramService->extractUserData($request->initData);
            
            if (!$userData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Не удалось извлечь данные пользователя'
                ], 400);
            }

            // Создаем или обновляем пользователя
            $user = User::updateOrCreate(
                ['telegram_id' => $userData['id']],
                [
                    'name' => $userData['first_name'] . ' ' . ($userData['last_name'] ?? ''),
                    'username' => $userData['username'] ?? null,
                    'photo_url' => $userData['photo_url'] ?? null,
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Данные успешно проверены',
                'data' => [
                    'user' => $user
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in WebAppController@validateInitData: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при проверке данных'
            ], 500);
        }
    }

    public function getUserInfo(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'telegram_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ошибка валидации',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('telegram_id', $request->telegram_id)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Пользователь не найден'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => $user
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in WebAppController@getUserInfo: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении информации о пользователе'
            ], 500);
        }
    }

    public function getMessages(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'telegram_id' => 'required|integer',
                'limit' => 'integer|min:1|max:100',
                'offset' => 'integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ошибка валидации',
                    'errors' => $validator->errors()
                ], 422);
            }

            $limit = $request->input('limit', 20);
            $offset = $request->input('offset', 0);

            // Здесь будет логика получения сообщений
            // Пока что возвращаем пустой список
            return response()->json([
                'status' => 'success',
                'data' => [
                    'messages' => [],
                    'total' => 0
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in WebAppController@getMessages: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении сообщений'
            ], 500);
        }
    }
} 