<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;

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

// Телеграм бот
Route::prefix('telegram')->name('telegram.')->group(function () {
    Route::post('set-webhook', [TelegramController::class, 'setWebhook'])->name('set-webhook');
    Route::post('webhook', [TelegramController::class, 'webhook'])->name('webhook');
    Route::get('webhook-info', [TelegramController::class, 'webhookInfo'])->name('webhook-info');
});

// Авторизация
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Публичные API для получения списков
Route::get('public/recipes', [RecipeController::class, 'index']);
Route::get('public/recipes/{id}', [RecipeController::class, 'show']);
Route::get('public/routes', [RouteController::class, 'index']);
Route::get('public/routes/{id}', [RouteController::class, 'show']);
Route::get('public/tags', [TagController::class, 'index']);

// Маршруты с аутентификацией
Route::middleware('auth:sanctum')->group(function () {
    // Пользователь
    Route::get('user', [AuthController::class, 'getUser']);
    Route::post('logout', [AuthController::class, 'logout']);
    
    // Рецепты
    Route::apiResource('recipes', RecipeController::class);
    
    // Маршруты
    Route::apiResource('routes', RouteController::class);
    
    // Теги
    Route::apiResource('tags', TagController::class);
});

require __DIR__.'/auth.php';

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_middleware')
])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); 