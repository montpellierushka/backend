<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class TelegramWebAppService
{
    private string $botToken;

    public function __construct()
    {
        $this->botToken = config('telegram.bot_token');
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
            $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

            return $hash === $params['hash'];
        } catch (\Exception $e) {
            Log::error('Error validating init data: ' . $e->getMessage());
            return false;
        }
    }

    public function getUserData(string $initData): ?array
    {
        try {
            $params = [];
            parse_str($initData, $params);

            if (!isset($params['user'])) {
                return null;
            }

            return json_decode($params['user'], true);
        } catch (\Exception $e) {
            Log::error('Error extracting user data: ' . $e->getMessage());
            return null;
        }
    }
} 