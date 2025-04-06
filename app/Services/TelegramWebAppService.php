<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class TelegramWebAppService
{
    private string $botToken;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
    }

    public function validateInitData(string $initData): bool
    {
        try {
            // Разбираем строку initData на параметры
            $params = [];
            parse_str($initData, $params);

            // Проверяем наличие необходимых параметров
            if (!isset($params['hash']) || !isset($params['user'])) {
                return false;
            }

            // Извлекаем hash из параметров
            $hash = $params['hash'];
            unset($params['hash']);

            // Сортируем параметры по алфавиту
            ksort($params);

            // Формируем строку для проверки
            $dataCheckString = '';
            foreach ($params as $key => $value) {
                $dataCheckString .= $key . '=' . $value . "\n";
            }
            $dataCheckString = rtrim($dataCheckString, "\n");

            // Вычисляем HMAC-SHA256
            $secretKey = hash('sha256', $this->botToken, true);
            $computedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

            // Сравниваем хеши
            return hash_equals($hash, $computedHash);
        } catch (\Exception $e) {
            Log::error('Error validating Telegram init data: ' . $e->getMessage());
            return false;
        }
    }

    public function extractUserData(string $initData): ?array
    {
        try {
            $params = [];
            parse_str($initData, $params);

            if (!isset($params['user'])) {
                return null;
            }

            $userData = json_decode($params['user'], true);
            return is_array($userData) ? $userData : null;
        } catch (\Exception $e) {
            Log::error('Error extracting user data: ' . $e->getMessage());
            return null;
        }
    }
} 