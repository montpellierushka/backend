<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;

Route::post('/telegram/webhook', [TelegramController::class, 'webhook']);
Route::post('/telegram/set-webhook', [TelegramController::class, 'setWebhook']);
Route::post('/telegram/remove-webhook', [TelegramController::class, 'removeWebhook']); 