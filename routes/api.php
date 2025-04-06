<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\WebAppController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RouteController;

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

// Recipe Routes
Route::prefix('recipes')->group(function () {
    Route::get('/', [RecipeController::class, 'index']);
    Route::get('/{recipe}', [RecipeController::class, 'show']);
    Route::get('/tags', [RecipeController::class, 'tags']);
    
    Route::middleware('auth:telegram')->group(function () {
        Route::post('/', [RecipeController::class, 'store']);
        Route::put('/{recipe}', [RecipeController::class, 'update']);
        Route::delete('/{recipe}', [RecipeController::class, 'destroy']);
    });
});

// Route Routes
Route::prefix('routes')->group(function () {
    Route::get('/', [RouteController::class, 'index']);
    Route::get('/{route}', [RouteController::class, 'show']);
    Route::get('/countries', [RouteController::class, 'countries']);
    
    Route::middleware('auth:telegram')->group(function () {
        Route::post('/', [RouteController::class, 'store']);
        Route::put('/{route}', [RouteController::class, 'update']);
        Route::delete('/{route}', [RouteController::class, 'destroy']);
    });
}); 