<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Message;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class TelegramService
{
    protected string $botToken;
    protected string $apiUrl;
    protected string $botUsername;
    protected string $webhookUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}";
        $this->botUsername = config('services.telegram.bot_username');
        $this->webhookUrl = config('services.telegram.webhook_url');

        if (empty($this->botToken)) {
            Log::error('Telegram bot token is not set');
        }
    }

    /**
     * Отправка сообщения в Telegram
     */
    public function sendMessage(int $chatId, string $text, array $options = []): array
    {
        if (empty($this->botToken)) {
            return [
                'success' => false,
                'message' => 'Bot token is not set'
            ];
        }

        try {
            $response = Http::post("{$this->apiUrl}/sendMessage", array_merge([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ], $options));

            $result = $response->json();

            if ($result['ok']) {
                return [
                    'success' => true,
                    'message' => 'Message sent successfully'
                ];
            }

            Log::error('Failed to send message', ['error' => $result['description']]);
            return [
                'success' => false,
                'message' => $result['description']
            ];
        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error sending message'
            ];
        }
    }

    /**
     * Установка вебхука
     */
    public function setWebhook(): array
    {
        if (empty($this->botToken)) {
            return [
                'success' => false,
                'message' => 'Bot token is not set'
            ];
        }

        try {
            $response = Http::post("{$this->apiUrl}/setWebhook", [
                'url' => $this->webhookUrl
            ]);

            $result = $response->json();

            if ($result['ok']) {
                Log::info('Webhook set successfully', ['url' => $this->webhookUrl]);
                return [
                    'success' => true,
                    'message' => 'Webhook set successfully'
                ];
            }

            Log::error('Failed to set webhook', ['error' => $result['description']]);
            return [
                'success' => false,
                'message' => $result['description']
            ];
        } catch (\Exception $e) {
            Log::error('Error setting webhook: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error setting webhook'
            ];
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
        $webAppUrl = config('services.telegram.web_app_url');
        
        switch ($command) {
            case 'start':
                $keyboard = [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '🚀 Открыть приложение',
                                'web_app' => ['url' => $webAppUrl]
                            ]
                        ],
                        [
                            [
                                'text' => '❓ Помощь',
                                'callback_data' => 'help'
                            ]
                        ]
                    ]
                ];

                $this->sendMessage($chat->telegram_id, 
                    "👋 Добро пожаловать!\n\n".
                    "Я ваш бот-помощник. Используйте кнопку ниже, чтобы открыть веб-приложение.",
                    ['reply_markup' => json_encode($keyboard)]
                );
                break;

            case 'help':
                $keyboard = [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '🚀 Открыть приложение',
                                'web_app' => ['url' => $webAppUrl]
                            ]
                        ]
                    ]
                ];

                $this->sendMessage($chat->telegram_id, 
                    "📝 Доступные команды:\n\n".
                    "/start - Начать работу с ботом\n".
                    "/help - Показать справку\n".
                    "/app - Открыть веб-приложение\n\n".
                    "Также вы можете использовать кнопку ниже для быстрого доступа к приложению:",
                    ['reply_markup' => json_encode($keyboard)]
                );
                break;

            case 'app':
                if ($webAppUrl) {
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                [
                                    'text' => '🚀 Открыть приложение',
                                    'web_app' => ['url' => $webAppUrl]
                                ]
                            ]
                        ]
                    ];

                    $this->sendMessage($chat->telegram_id, 
                        "🌐 Нажмите на кнопку ниже, чтобы открыть веб-приложение:",
                        ['reply_markup' => json_encode($keyboard)]
                    );
                } else {
                    $this->sendMessage($chat->telegram_id, '⚠️ Веб-приложение временно недоступно.');
                }
                break;

            default:
                $keyboard = [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '❓ Помощь',
                                'callback_data' => 'help'
                            ]
                        ]
                    ]
                ];

                $this->sendMessage($chat->telegram_id, 
                    "❌ Неизвестная команда.\n\nИспользуйте /help для получения списка доступных команд.",
                    ['reply_markup' => json_encode($keyboard)]
                );
                break;
        }
    }

    public function webhook($update)
    {
        try {
            if (isset($update['message'])) {
                $this->handleMessage($update);
                return ['ok' => true];
            } 
            
            if (isset($update['callback_query'])) {
                $this->handleCallbackQuery($update['callback_query']);
                return ['ok' => true];
            }

            Log::warning('Unhandled update type', ['update' => $update]);
            return ['ok' => true];
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    protected function handleCallbackQuery($callbackQuery)
    {
        try {
            $user = $this->getOrCreateUser($callbackQuery['from']);
            $message = $callbackQuery['message'];
            $data = $callbackQuery['data'];

            // Создаем запись о callback-запросе
            Message::create([
                'user_id' => $user->id,
                'chat_id' => $message['chat']['id'],
                'message_id' => $message['message_id'],
                'text' => $data,
                'type' => 'callback',
                'data' => json_encode($callbackQuery)
            ]);

            // Обрабатываем различные callback-запросы
            switch ($data) {
                case 'help':
                    $webAppUrl = config('services.telegram.web_app_url');
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                [
                                    'text' => '🚀 Открыть приложение',
                                    'web_app' => ['url' => $webAppUrl]
                                ]
                            ]
                        ]
                    ];

                    // Отправляем ответ на callback-запрос
                    Http::post("{$this->apiUrl}/answerCallbackQuery", [
                        'callback_query_id' => $callbackQuery['id'],
                        'text' => 'Открываю справку...'
                    ]);

                    // Отправляем новое сообщение со справкой
                    $this->sendMessage($message['chat']['id'], 
                        "📝 Доступные команды:\n\n".
                        "/start - Начать работу с ботом\n".
                        "/help - Показать справку\n".
                        "/app - Открыть веб-приложение\n\n".
                        "Также вы можете использовать кнопку ниже для быстрого доступа к приложению:",
                        ['reply_markup' => json_encode($keyboard)]
                    );
                    break;

                default:
                    // Отправляем ответ на неизвестный callback-запрос
                    Http::post("{$this->apiUrl}/answerCallbackQuery", [
                        'callback_query_id' => $callbackQuery['id'],
                        'text' => 'Неизвестная команда'
                    ]);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Error handling callback query: ' . $e->getMessage());
            
            // Отправляем ответ об ошибке
            try {
                Http::post("{$this->apiUrl}/answerCallbackQuery", [
                    'callback_query_id' => $callbackQuery['id'],
                    'text' => 'Произошла ошибка',
                    'show_alert' => true
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending callback answer: ' . $e->getMessage());
            }
        }
    }

    protected function getOrCreateUser($userData)
    {
        return TelegramUser::firstOrCreate(
            ['telegram_id' => $userData['id']],
            [
                'username' => $userData['username'] ?? null,
                'first_name' => $userData['first_name'] ?? null,
                'last_name' => $userData['last_name'] ?? null,
                'language_code' => $userData['language_code'] ?? null,
            ]
        );
    }

    protected function getOrCreateChat($chatData)
    {
        return Chat::firstOrCreate(
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

    public function validateInitData($initData)
    {
        try {
            $data = [];
            parse_str($initData, $data);
            
            if (!isset($data['hash'])) {
                return false;
            }

            $hash = $data['hash'];
            unset($data['hash']);

            ksort($data);
            $dataString = http_build_query($data);
            
            $secretKey = hash('sha256', $this->botToken, true);
            $calculatedHash = hash_hmac('sha256', $dataString, $secretKey);

            return hash_equals($hash, $calculatedHash);
        } catch (\Exception $e) {
            Log::error('Error validating init data: ' . $e->getMessage());
            return false;
        }
    }

    public function getUserFromInitData($initData)
    {
        try {
            $data = [];
            parse_str($initData, $data);
            
            if (!isset($data['user'])) {
                return null;
            }

            return json_decode($data['user'], true);
        } catch (\Exception $e) {
            Log::error('Error getting user from init data: ' . $e->getMessage());
            return null;
        }
    }
} 