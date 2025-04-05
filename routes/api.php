<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\WebAppController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Telegram Bot Routes
Route::prefix('telegram')->group(function () {
    Route::post('set-webhook', [TelegramController::class, 'setWebhook']);
    Route::post('webhook', [TelegramController::class, 'webhook']);
    Route::get('webhook-info', [TelegramController::class, 'webhookInfo']);
});

// Web App Routes
Route::prefix('web-app')->middleware('telegram.webapp')->group(function () {
    Route::post('validate-init-data', [WebAppController::class, 'validateInitData']);
    Route::get('user-info', [WebAppController::class, 'getUserInfo']);
    Route::get('messages', [WebAppController::class, 'getMessages']);
}); 