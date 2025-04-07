<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class TelegramWebAppService
{
    private string $botToken;

    public function __construct()
    {
        $this->botToken = config('telegram.bot_token', 'test_bot_token');
    }

    /**
     * Проверяет подлинность данных инициализации Telegram Web App
     */
    public function validateInitData(string $initData): bool
    {
        try {
            // В режиме разработки всегда возвращаем true
            if (config('app.env') === 'local') {
                return true;
            }

            // Разбираем строку initData на параметры
            $params = [];
            parse_str($initData, $params);

            // Проверяем наличие необходимых параметров
            if (!isset($params['hash']) || !isset($params['user'])) {
                Log::warning('Missing required parameters in init data', ['params' => $params]);
                return false;
            }

            // Извлекаем hash из параметров
            $hash = $params['hash'];
            unset($params['hash']);

            // Сортируем параметры по ключам
            ksort($params);

            // Формируем строку для проверки
            $dataCheckString = '';
            foreach ($params as $key => $value) {
                $dataCheckString .= $key . '=' . $value . "\n";
            }
            $dataCheckString = trim($dataCheckString);

            // Вычисляем HMAC-SHA256
            $secretKey = hash('sha256', $this->botToken, true);
            $computedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

            // Сравниваем хеши
            $isValid = hash_equals($hash, $computedHash);
            
            if (!$isValid) {
                Log::warning('Invalid hash in init data', [
                    'received' => $hash,
                    'computed' => $computedHash,
                    'data' => $dataCheckString
                ]);
            }

            return $isValid;
        } catch (\Exception $e) {
            Log::error('Error validating init data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Извлекает данные пользователя из initData
     */
    public function getUserData(string $initData): ?array
    {
        try {
            // В режиме разработки возвращаем тестовые данные
            if (config('app.env') === 'local') {
                return [
                    'id' => 123456789,
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'username' => 'testuser',
                    'language_code' => 'en',
                    'is_premium' => true,
                    'photo_url' => 'https://example.com/photo.jpg'
                ];
            }

            $params = [];
            parse_str($initData, $params);

            if (!isset($params['user'])) {
                Log::warning('No user data in init data');
                return null;
            }

            $userData = json_decode($params['user'], true);
            
            if (!is_array($userData)) {
                Log::warning('Invalid user data format', ['user' => $params['user']]);
                return null;
            }

            return $userData;
        } catch (\Exception $e) {
            Log::error('Error extracting user data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
} 