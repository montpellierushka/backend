<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
{
    /**
     * Получение списка избранных рецептов и маршрутов
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            $recipes = $user->favoriteRecipes()
                ->with(['country', 'user'])
                ->withCount('favorites')
                ->paginate(12);

            $routes = $user->favoriteRoutes()
                ->with(['countries', 'user'])
                ->withCount('favorites')
                ->paginate(12);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'recipes' => $recipes,
                    'routes' => $routes
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in FavoriteController@index: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении списка избранного'
            ], 500);
        }
    }

    /**
     * Добавление рецепта в избранное
     */
    public function addRecipe(Recipe $recipe)
    {
        try {
            $user = auth()->user();

            if ($user->favoriteRecipes()->where('recipe_id', $recipe->id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Рецепт уже добавлен в избранное'
                ], 400);
            }

            $user->favoriteRecipes()->attach($recipe->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Рецепт успешно добавлен в избранное'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in FavoriteController@addRecipe: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при добавлении рецепта в избранное'
            ], 500);
        }
    }

    /**
     * Удаление рецепта из избранного
     */
    public function removeRecipe(Recipe $recipe)
    {
        try {
            $user = auth()->user();

            if (!$user->favoriteRecipes()->where('recipe_id', $recipe->id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Рецепт не найден в избранном'
                ], 400);
            }

            $user->favoriteRecipes()->detach($recipe->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Рецепт успешно удален из избранного'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in FavoriteController@removeRecipe: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при удалении рецепта из избранного'
            ], 500);
        }
    }

    /**
     * Добавление маршрута в избранное
     */
    public function addRoute(Route $route)
    {
        try {
            $user = auth()->user();

            if ($user->favoriteRoutes()->where('route_id', $route->id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Маршрут уже добавлен в избранное'
                ], 400);
            }

            $user->favoriteRoutes()->attach($route->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Маршрут успешно добавлен в избранное'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in FavoriteController@addRoute: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при добавлении маршрута в избранное'
            ], 500);
        }
    }

    /**
     * Удаление маршрута из избранного
     */
    public function removeRoute(Route $route)
    {
        try {
            $user = auth()->user();

            if (!$user->favoriteRoutes()->where('route_id', $route->id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Маршрут не найден в избранном'
                ], 400);
            }

            $user->favoriteRoutes()->detach($route->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Маршрут успешно удален из избранного'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in FavoriteController@removeRoute: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при удалении маршрута из избранного'
            ], 500);
        }
    }
} 