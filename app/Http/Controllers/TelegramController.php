<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    protected $botToken;
    protected $botUsername;
    protected $webhookUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->botUsername = config('services.telegram.bot_username');
        $this->webhookUrl = config('services.telegram.webhook_url');
    }

    public function setWebhook()
    {
        try {
            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/setWebhook", [
                'url' => $this->webhookUrl
            ]);

            Log::info('Webhook set response:', ['response' => $response->json()]);

            return response()->json([
                'success' => $response->successful(),
                'message' => $response->json()
            ]);
        } catch (\Exception $e) {
            Log::error('Error setting webhook:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function webhook(Request $request)
    {
        try {
            $update = $request->all();
            Log::info('Telegram webhook received:', ['update' => $update]);

            // Здесь будет логика обработки сообщений от бота
            $message = $update['message']['text'] ?? '';
            $chatId = $update['message']['chat']['id'] ?? null;

            if ($chatId && $message) {
                $this->sendMessage($chatId, "Вы написали: $message");
            }

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Error processing webhook:', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false], 500);
        }
    }

    public function webhookInfo()
    {
        try {
            $response = Http::get("https://api.telegram.org/bot{$this->botToken}/getWebhookInfo");
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    protected function sendMessage($chatId, $text)
    {
        return Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text
        ]);
    }
} 