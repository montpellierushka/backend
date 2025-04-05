<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;

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

Route::prefix('telegram')->group(function () {
    Route::post('set-webhook', [TelegramController::class, 'setWebhook']);
    Route::post('webhook', [TelegramController::class, 'webhook']);
    Route::get('webhook-info', [TelegramController::class, 'webhookInfo']);
}); 