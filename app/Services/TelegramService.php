<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Message;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $botToken;
    protected string $apiUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    /**
     * Отправка сообщения в Telegram
     */
    public function sendMessage(int $chatId, string $text, array $options = []): array
    {
        try {
            $response = Http::post("{$this->apiUrl}/sendMessage", array_merge([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ], $options));

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Telegram API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Telegram Service Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Установка вебхука
     */
    public function setWebhook(string $url): array
    {
        try {
            $response = Http::post("{$this->apiUrl}/setWebhook", [
                'url' => $url,
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Set Webhook Error', [
                'message' => $e->getMessage(),
            ]);

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Получение информации о вебхуке
     */
    public function getWebhookInfo(): array
    {
        try {
            $response = Http::get("{$this->apiUrl}/getWebhookInfo");
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Get Webhook Info Error', [
                'message' => $e->getMessage(),
            ]);

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Получение информации о веб-приложении
     */
    public function getWebAppInfo(): array
    {
        try {
            $response = Http::get("{$this->apiUrl}/getWebAppInfo");
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Get WebApp Info Error', [
                'message' => $e->getMessage(),
            ]);

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Проверка данных веб-приложения
     */
    public function validateWebAppData(string $initData): bool
    {
        try {
            $response = Http::post("{$this->apiUrl}/validateWebAppData", [
                'init_data' => $initData,
            ]);

            return $response->json()['ok'] ?? false;
        } catch (\Exception $e) {
            Log::error('Validate WebApp Data Error', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Обработка входящего сообщения
     */
    public function handleMessage(array $update): void
    {
        try {
            $message = $update['message'] ?? null;
            if (!$message) {
                return;
            }

            // Создание или обновление пользователя
            $telegramUser = $this->createOrUpdateTelegramUser($message['from']);

            // Создание или обновление чата
            $chat = $this->createOrUpdateChat($message['chat']);

            // Создание сообщения
            $this->createMessage($message, $telegramUser, $chat);

            // Обработка команд
            if (isset($message['text']) && str_starts_with($message['text'], '/')) {
                $this->handleCommand($message['text'], $telegramUser, $chat);
            }
        } catch (\Exception $e) {
            Log::error('Handle Message Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Создание или обновление пользователя Telegram
     */
    protected function createOrUpdateTelegramUser(array $userData): TelegramUser
    {
        return TelegramUser::updateOrCreate(
            ['telegram_id' => $userData['id']],
            [
                'username' => $userData['username'] ?? null,
                'first_name' => $userData['first_name'] ?? null,
                'last_name' => $userData['last_name'] ?? null,
                'language_code' => $userData['language_code'] ?? null,
                'is_bot' => $userData['is_bot'] ?? false,
            ]
        );
    }

    /**
     * Создание или обновление чата
     */
    protected function createOrUpdateChat(array $chatData): Chat
    {
        return Chat::updateOrCreate(
            ['telegram_id' => $chatData['id']],
            [
                'type' => $chatData['type'],
                'title' => $chatData['title'] ?? null,
                'username' => $chatData['username'] ?? null,
                'first_name' => $chatData['first_name'] ?? null,
                'last_name' => $chatData['last_name'] ?? null,
            ]
        );
    }

    /**
     * Создание сообщения
     */
    protected function createMessage(array $messageData, TelegramUser $telegramUser, Chat $chat): Message
    {
        return Message::create([
            'telegram_id' => $messageData['message_id'],
            'text' => $messageData['text'] ?? null,
            'date' => date('Y-m-d H:i:s', $messageData['date']),
            'telegram_user_id' => $telegramUser->id,
            'chat_id' => $chat->id,
            'reply_to_message_id' => $messageData['reply_to_message']['message_id'] ?? null,
            'is_command' => isset($messageData['text']) && str_starts_with($messageData['text'], '/'),
        ]);
    }

    /**
     * Обработка команд
     */
    protected function handleCommand(string $command, TelegramUser $telegramUser, Chat $chat): void
    {
        $command = strtolower(trim($command, '/'));
        
        switch ($command) {
            case 'start':
                $this->sendMessage($chat->telegram_id, 'Добро пожаловать! Я ваш бот-помощник.');
                break;
            case 'help':
                $this->sendMessage($chat->telegram_id, 'Доступные команды:
/start - Начать работу с ботом
/help - Показать справку
/app - Открыть веб-приложение');
                break;
            case 'app':
                $webAppUrl = config('services.telegram.web_app_url');
                if ($webAppUrl) {
                    $this->sendMessage($chat->telegram_id, 'Откройте веб-приложение:', [
                        'reply_markup' => json_encode([
                            'inline_keyboard' => [[
                                [
                                    'text' => 'Открыть приложение',
                                    'web_app' => ['url' => $webAppUrl]
                                ]
                            ]]
                        ])
                    ]);
                } else {
                    $this->sendMessage($chat->telegram_id, 'Веб-приложение не настроено.');
                }
                break;
            default:
                $this->sendMessage($chat->telegram_id, 'Неизвестная команда. Используйте /help для получения списка команд.');
                break;
        }
    }
} 