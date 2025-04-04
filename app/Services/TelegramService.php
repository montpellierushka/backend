<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

class TelegramService
{
    protected $telegram;

    public function __construct()
    {
        $this->telegram = new Api(config('services.telegram.bot_token'));
    }

    public function handleWebhook(Update $update)
    {
        try {
            $message = $update->getMessage();
            $chatId = $message->getChat()->getId();
            $text = $message->getText();
            $username = $message->getFrom()->getUsername();

            // Регистрация или авторизация пользователя
            $user = $this->handleUser($chatId, $username);

            // Обработка команд
            if (str_starts_with($text, '/')) {
                return $this->handleCommand($text, $chatId, $user);
            }

            // Обработка обычных сообщений
            return $this->handleMessage($text, $chatId, $user);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error: ' . $e->getMessage());
            return $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Произошла ошибка. Пожалуйста, попробуйте позже.'
            ]);
        }
    }

    protected function handleUser($telegramId, $username)
    {
        $user = User::where('telegram_id', $telegramId)->first();

        if (!$user) {
            $user = User::create([
                'name' => $username,
                'email' => $telegramId . '@telegram.user',
                'password' => bcrypt(str_random(16)),
                'telegram_id' => $telegramId,
                'telegram_username' => $username
            ]);
        }

        return $user;
    }

    protected function handleCommand($text, $chatId, $user)
    {
        $command = explode(' ', $text)[0];

        switch ($command) {
            case '/start':
                return $this->sendWelcomeMessage($chatId);
            case '/help':
                return $this->sendHelpMessage($chatId);
            case '/recipes':
                return $this->handleRecipesCommand($text, $chatId, $user);
            case '/routes':
                return $this->handleRoutesCommand($chatId, $user);
            default:
                return $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Неизвестная команда. Используйте /help для просмотра доступных команд.'
                ]);
        }
    }

    protected function handleMessage($text, $chatId, $user)
    {
        return $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Используйте команды для взаимодействия с ботом. /help - для просмотра списка команд.'
        ]);
    }

    protected function sendWelcomeMessage($chatId)
    {
        return $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Добро пожаловать в бот для планирования путешествий! 🗺️' . PHP_EOL . PHP_EOL .
                     'Доступные команды:' . PHP_EOL .
                     '/recipes - Поиск рецептов' . PHP_EOL .
                     '/routes - Просмотр маршрутов' . PHP_EOL .
                     '/help - Помощь'
        ]);
    }

    protected function sendHelpMessage($chatId)
    {
        return $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Список доступных команд:' . PHP_EOL . PHP_EOL .
                     '/start - Начать работу с ботом' . PHP_EOL .
                     '/recipes - Поиск рецептов по стране или тегу' . PHP_EOL .
                     '/routes - Просмотр маршрутов' . PHP_EOL .
                     '/help - Показать это сообщение' . PHP_EOL . PHP_EOL .
                     'Примеры использования:' . PHP_EOL .
                     '/recipes Италия - поиск рецептов итальянской кухни' . PHP_EOL .
                     '/recipes #паста - поиск рецептов с тегом "паста"'
        ]);
    }

    protected function handleRecipesCommand($text, $chatId, $user)
    {
        $searchQuery = trim(substr($text, strlen('/recipes ')));

        if (empty($searchQuery)) {
            return $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Пожалуйста, укажите страну или тег для поиска. Например: /recipes Италия или /recipes #паста'
            ]);
        }

        // Здесь будет логика поиска рецептов
        // Пока отправляем заглушку
        return $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Поиск рецептов по запросу: ' . $searchQuery
        ]);
    }

    protected function handleRoutesCommand($chatId, $user)
    {
        // Здесь будет логика получения маршрутов
        // Пока отправляем заглушку
        return $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Список доступных маршрутов будет здесь'
        ]);
    }

    public function setWebhook($url)
    {
        return $this->telegram->setWebhook([
            'url' => $url
        ]);
    }

    public function removeWebhook()
    {
        return $this->telegram->removeWebhook();
    }
} 