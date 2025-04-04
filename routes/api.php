<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_middleware')
])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });
});

Route::prefix('telegram')->group(function () {
    Route::post('webhook', [TelegramController::class, 'webhook']);
    Route::post('set-webhook', [TelegramController::class, 'setWebhook']);
    Route::post('remove-webhook', [TelegramController::class, 'removeWebhook']);
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