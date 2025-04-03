<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function webhook(Request $request)
    {
        $update = Telegram::commandsHandler(true);
        return $this->telegramService->handleWebhook($update);
    }

    public function setWebhook()
    {
        $response = Telegram::setWebhook([
            'url' => config('services.telegram.webhook_url')
        ]);

        return response()->json($response);
    }

    public function removeWebhook()
    {
        $response = Telegram::removeWebhook();
        return response()->json($response);
    }
} 