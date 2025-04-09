<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;

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

// Public Routes
Route::get('/recipes', [RecipeController::class, 'index']);
Route::get('/recipes/{recipe}', [RecipeController::class, 'show']);
Route::get('/recipes/country/{country}', [RecipeController::class, 'byCountry']);
Route::get('/recipes/tag/{tag}', [RecipeController::class, 'byTag']);

Route::get('/routes', [RouteController::class, 'index']);
Route::get('/routes/{route}', [RouteController::class, 'show']);
Route::get('/routes/country/{country}', [RouteController::class, 'byCountry']);

Route::get('/tags', [TagController::class, 'index']);
Route::get('/tags/{tag}', [TagController::class, 'show']);
Route::get('/tags/{tag}/recipes', [TagController::class, 'recipes']);

Route::get('/countries', [CountryController::class, 'index']);
Route::get('/countries/{country}', [CountryController::class, 'show']);
Route::get('/countries/{country}/recipes', [CountryController::class, 'recipes']);
Route::get('/countries/{country}/routes', [CountryController::class, 'routes']);

Route::get('/search', [SearchController::class, 'search']);

Route::get('/stats', [StatsController::class, 'index']);
Route::get('/stats/countries', [StatsController::class, 'countries']);
Route::get('/stats/recipes', [StatsController::class, 'recipes']);
Route::get('/stats/routes', [StatsController::class, 'routes']);

// Избранное
Route::get('/favorites', [FavoriteController::class, 'index']);
Route::post('/favorites/recipes/{recipe}', [FavoriteController::class, 'addRecipe']);
Route::delete('/favorites/recipes/{recipe}', [FavoriteController::class, 'removeRecipe']);
Route::post('/favorites/routes/{route}', [FavoriteController::class, 'addRoute']);
Route::delete('/favorites/routes/{route}', [FavoriteController::class, 'removeRoute']);

// Лайки
Route::post('/likes/recipes/{recipe}', [LikeController::class, 'toggleRecipe']);
Route::post('/likes/routes/{route}', [LikeController::class, 'toggleRoute']);

// Комментарии
Route::get('/recipes/{recipe}/comments', [CommentController::class, 'recipeComments']);
Route::post('/recipes/{recipe}/comments', [CommentController::class, 'addRecipeComment']);
Route::get('/routes/{route}/comments', [CommentController::class, 'routeComments']);
Route::post('/routes/{route}/comments', [CommentController::class, 'addRouteComment']);
Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

// Загрузка файлов
Route::post('/upload/image', [UploadController::class, 'image']);
Route::delete('/upload/{path}', [UploadController::class, 'delete']);

// Защищенные маршруты
    // Рецепты
    Route::post('/recipes', [RecipeController::class, 'store']);
    Route::put('/recipes/{recipe}', [RecipeController::class, 'update']);
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy']);

    // Маршруты
    Route::post('/routes', [RouteController::class, 'store']);
    Route::put('/routes/{route}', [RouteController::class, 'update']);
    Route::delete('/routes/{route}', [RouteController::class, 'destroy']);

    // Пользователи
    Route::get('/users/profile', [UserController::class, 'profile']);
    Route::put('/users/profile', [UserController::class, 'updateProfile']);
    Route::get('/users/{user}/recipes', [UserController::class, 'recipes']);
    Route::get('/users/{user}/routes', [UserController::class, 'routes']);

// Telegram Bot Routes
Route::prefix('telegram')->group(function () {
    Route::post('set-webhook', [TelegramController::class, 'setWebhook']);
    Route::post('webhook', [TelegramController::class, 'webhook']);
    Route::get('webhook-info', [TelegramController::class, 'webhookInfo']);
});



    Route::prefix('recipes')->group(function () {
        Route::get('/', [RecipeController::class, 'index']);
        Route::get('/{recipe}', [RecipeController::class, 'show']);
        Route::get('/tags', [RecipeController::class, 'tags']);
        Route::post('/', [RecipeController::class, 'store']);
        Route::put('/{recipe}', [RecipeController::class, 'update']);
        Route::delete('/{recipe}', [RecipeController::class, 'destroy']);
        Route::get('/country/{country}', [RecipeController::class, 'byCountry']);
        Route::get('/tag/{tag}', [RecipeController::class, 'byTag']);
    });

    // Route Routes
    Route::prefix('routes')->group(function () {
        Route::get('/', [RouteController::class, 'index']);
        Route::get('/{route}', [RouteController::class, 'show']);
        Route::post('/', [RouteController::class, 'store']);
        Route::put('/{route}', [RouteController::class, 'update']);
        Route::delete('/{route}', [RouteController::class, 'destroy']);
        Route::get('/country/{country}', [RouteController::class, 'byCountry']);
    });

    // Country Routes
    Route::prefix('countries')->group(function () {
        Route::get('/', [CountryController::class, 'index']);
        Route::get('/{country}', [CountryController::class, 'show']);
        Route::get('/{country}/recipes', [CountryController::class, 'recipes']);
        Route::get('/{country}/routes', [CountryController::class, 'routes']);
    });

    // Tag Routes
    Route::prefix('tags')->group(function () {
        Route::get('/', [TagController::class, 'index']);
        Route::get('/{tag}', [TagController::class, 'show']);
        Route::get('/{tag}/recipes', [TagController::class, 'recipes']);
    });

    // Favorite Routes
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::post('/recipes/{recipe}', [FavoriteController::class, 'addRecipe']);
        Route::delete('/recipes/{recipe}', [FavoriteController::class, 'removeRecipe']);
        Route::post('/routes/{route}', [FavoriteController::class, 'addRoute']);
        Route::delete('/routes/{route}', [FavoriteController::class, 'removeRoute']);
    });

    // User Routes
    Route::prefix('users')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::get('/{user}/recipes', [UserController::class, 'recipes']);
        Route::get('/{user}/routes', [UserController::class, 'routes']);
    });

    // Search Routes
    Route::prefix('search')->group(function () {
        Route::get('/', [SearchController::class, 'search']);
    });

    // Upload Routes
    Route::prefix('upload')->group(function () {
        Route::post('/image', [UploadController::class, 'uploadImage']);
        Route::delete('/{path}', [UploadController::class, 'delete']);
    });

    // Stats Routes
    Route::prefix('stats')->group(function () {
        Route::get('/', [StatsController::class, 'index']);
        Route::get('/countries', [StatsController::class, 'countries']);
        Route::get('/recipes', [StatsController::class, 'recipes']);
        Route::get('/routes', [StatsController::class, 'routes']);
    });

    // Comment Routes
    Route::prefix('comments')->group(function () {
        Route::get('/recipe/{recipe}', [CommentController::class, 'index']);
        Route::post('/recipe/{recipe}', [CommentController::class, 'store']);
        Route::put('/{comment}', [CommentController::class, 'update']);
        Route::delete('/{comment}', [CommentController::class, 'destroy']);
    });

    // Like Routes
    Route::prefix('likes')->group(function () {
        Route::post('/recipe/{recipe}', [LikeController::class, 'store']);
        Route::delete('/recipe/{recipe}', [LikeController::class, 'destroy']);
    });

