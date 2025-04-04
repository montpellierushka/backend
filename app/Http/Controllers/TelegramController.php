<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\Request;
use Telegram\Bot\Objects\Update;

class TelegramController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function webhook(Request $request)
    {
        $update = new Update($request->all());
        return $this->telegramService->handleWebhook($update);
    }

    public function setWebhook()
    {
        $webhookUrl = config('services.telegram.webhook_url');
        $response = $this->telegramService->setWebhook($webhookUrl);
        
        return response()->json([
            'success' => $response->isOk(),
            'message' => $response->getDescription()
        ]);
    }

    public function removeWebhook()
    {
        $response = $this->telegramService->removeWebhook();
        
        return response()->json([
            'success' => $response->isOk(),
            'message' => $response->getDescription()
        ]);
    }
} 