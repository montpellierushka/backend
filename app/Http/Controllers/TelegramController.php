<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    protected $botToken;
    protected $webhookUrl;
    protected $botUsername;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->webhookUrl = config('services.telegram.webhook_url');
        $this->botUsername = config('services.telegram.bot_username');
    }

    public function setWebhook()
    {
        try {
            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/setWebhook", [
                'url' => $this->webhookUrl
            ]);

            Log::info('Webhook setting response:', $response->json());

            return response()->json([
                'success' => $response->successful(),
                'data' => $response->json()
            ]);
        } catch (\Exception $e) {
            Log::error('Error setting webhook: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function webhook(Request $request)
    {
        try {
            $update = $request->all();
            Log::info('Telegram update:', $update);

            // Получаем сообщение
            $message = $update['message'] ?? null;
            if (!$message) {
                return response()->json(['success' => true]);
            }

            $chatId = $message['chat']['id'] ?? null;
            $text = $message['text'] ?? '';

            // Простой ответ на сообщение
            if ($chatId) {
                $this->sendMessage($chatId, "Вы написали: $text");
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error processing webhook: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function webhookInfo()
    {
        try {
            $response = Http::get("https://api.telegram.org/bot{$this->botToken}/getWebhookInfo");
            
            return response()->json([
                'success' => true,
                'data' => $response->json()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting webhook info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    protected function sendMessage($chatId, $text)
    {
        try {
            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text
            ]);

            Log::info('Message sent:', $response->json());
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            return null;
        }
    }
}
