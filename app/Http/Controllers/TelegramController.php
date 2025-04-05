<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    protected TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Установка вебхука
     */
    public function setWebhook(Request $request)
    {
        $webhookUrl = config('services.telegram.webhook_url');
        
        if (!$webhookUrl) {
            return response()->json([
                'ok' => false,
                'error' => 'Webhook URL не настроен',
            ], 500);
        }

        $result = $this->telegramService->setWebhook($webhookUrl);
        
        return response()->json($result);
    }

    /**
     * Получение информации о вебхуке
     */
    public function webhookInfo()
    {
        $result = $this->telegramService->getWebhookInfo();
        
        return response()->json($result);
    }

    /**
     * Обработка входящих сообщений
     */
    public function webhook(Request $request)
    {
        try {
            $update = $request->all();
            Log::info('Telegram Webhook Update', $update);

            $this->telegramService->handleMessage($update);

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Webhook Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
