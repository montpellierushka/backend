<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\WebAppController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
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
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Telegram Bot Routes
Route::prefix('telegram')->group(function () {
    Route::post('set-webhook', [TelegramController::class, 'setWebhook']);
    Route::post('webhook', [TelegramController::class, 'webhook']);
    Route::get('webhook-info', [TelegramController::class, 'webhookInfo']);
});

// Web App Routes
Route::prefix('web-app')->group(function () {
    Route::post('validate-init-data', [WebAppController::class, 'validateInitData']);
    Route::middleware('telegram.auth')->group(function () {
        Route::get('user-info', [WebAppController::class, 'getUserInfo']);
        Route::get('messages', [WebAppController::class, 'getMessages']);
    });
});

// Protected Routes (require Telegram authentication)
Route::middleware('telegram.auth')->group(function () {
    // Recipe Routes
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
        Route::post('/{recipe}', [FavoriteController::class, 'store']);
        Route::delete('/{recipe}', [FavoriteController::class, 'destroy']);
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
});

// Страны
Route::get('countries', [CountryController::class, 'index']);
Route::get('countries/{country}', [CountryController::class, 'show']);
Route::get('countries/{country}/recipes', [CountryController::class, 'recipes']);
Route::get('countries/{country}/routes', [CountryController::class, 'routes']);

// Теги
Route::apiResource('tags', TagController::class);
Route::get('tags/{tag}/recipes', [TagController::class, 'recipes']);

// Избранное
Route::middleware('auth:sanctum')->group(function () {
    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::post('favorites/{recipe}', [FavoriteController::class, 'store']);
    Route::delete('favorites/{recipe}', [FavoriteController::class, 'destroy']);
});

// Пользователи
Route::apiResource('users', UserController::class)->only(['show', 'update']);
Route::get('users/{user}/recipes', [UserController::class, 'recipes']);
Route::get('users/{user}/routes', [UserController::class, 'routes']);

// Поиск
Route::get('search', [SearchController::class, 'index']);

// Загрузка файлов
Route::middleware('auth:sanctum')->group(function () {
    Route::post('upload/image', [UploadController::class, 'image']);
    Route::delete('upload/{path}', [UploadController::class, 'delete']);
});

// Статистика
Route::get('stats', [StatsController::class, 'index']);
Route::get('stats/countries', [StatsController::class, 'countries']);
Route::get('stats/recipes', [StatsController::class, 'recipes']);
Route::get('stats/routes', [StatsController::class, 'routes']);

// Комментарии
Route::get('recipes/{recipe}/comments', [CommentController::class, 'recipeComments']);
Route::get('routes/{route}/comments', [CommentController::class, 'routeComments']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('recipes/{recipe}/comments', [CommentController::class, 'addRecipeComment']);
    Route::post('routes/{route}/comments', [CommentController::class, 'addRouteComment']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);
});

// Лайки
Route::middleware('auth:sanctum')->group(function () {
    Route::post('recipes/{recipe}/like', [LikeController::class, 'likeRecipe']);
    Route::delete('recipes/{recipe}/like', [LikeController::class, 'unlikeRecipe']);
    Route::post('routes/{route}/like', [LikeController::class, 'likeRoute']);
    Route::delete('routes/{route}/like', [LikeController::class, 'unlikeRoute']);
    Route::post('comments/{comment}/like', [LikeController::class, 'likeComment']);
    Route::delete('comments/{comment}/like', [LikeController::class, 'unlikeComment']);
});

// Публичные маршруты
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Маршруты для рецептов
Route::get('/recipes', [RecipeController::class, 'index']);
Route::get('/recipes/{recipe}', [RecipeController::class, 'show']);
Route::get('/recipes/country/{country}', [RecipeController::class, 'byCountry']);
Route::get('/recipes/tag/{tag}', [RecipeController::class, 'byTag']);

// Маршруты для маршрутов
Route::get('/routes', [RouteController::class, 'index']);
Route::get('/routes/{route}', [RouteController::class, 'show']);
Route::get('/routes/country/{country}', [RouteController::class, 'byCountry']);

// Маршруты для тегов
Route::get('/tags', [TagController::class, 'index']);

// Маршруты для поиска
Route::get('/search', [SearchController::class, 'search']);

// Маршруты для статистики
Route::get('/stats', [StatsController::class, 'index']);
Route::get('/stats/countries', [StatsController::class, 'countries']);
Route::get('/stats/recipes', [StatsController::class, 'recipes']);
Route::get('/stats/routes', [StatsController::class, 'routes']);

// Защищенные маршруты (требуют аутентификации через Telegram)
Route::middleware('telegram.auth')->group(function () {
    // Рецепты
    Route::post('/recipes', [RecipeController::class, 'store']);
    Route::put('/recipes/{recipe}', [RecipeController::class, 'update']);
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy']);

    // Маршруты
    Route::post('/routes', [RouteController::class, 'store']);
    Route::put('/routes/{route}', [RouteController::class, 'update']);
    Route::delete('/routes/{route}', [RouteController::class, 'destroy']);

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
});

// Публичные маршруты
Route::post('/web-app/validate-init-data', [WebAppController::class, 'validateInitData']);

// Защищенные маршруты
Route::middleware('telegram.auth')->group(function () {
    Route::get('/web-app/user-info', [WebAppController::class, 'getUserInfo']);
    Route::get('/web-app/messages', [WebAppController::class, 'getMessages']);
    
    // Здесь будут другие защищенные маршруты
}); 