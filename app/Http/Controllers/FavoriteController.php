<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
{
    /**
     * Получение списка избранных рецептов
     */
    public function recipes()
    {
        try {
            $recipes = auth()->user()->favoriteRecipes()
                ->with(['country', 'tags', 'user'])
                ->withCount('favorites')
                ->paginate(12);

            return response()->json([
                'status' => 'success',
                'data' => $recipes
            ]);
        } catch (\Exception $e) {
            Log::error('Error in FavoriteController@recipes: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении списка избранных рецептов'
            ], 500);
        }
    }

    /**
     * Добавление рецепта в избранное
     */
    public function addRecipe(Recipe $recipe)
    {
        try {
            auth()->user()->favoriteRecipes()->attach($recipe->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Рецепт добавлен в избранное'
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
            auth()->user()->favoriteRecipes()->detach($recipe->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Рецепт удален из избранного'
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
     * Получение списка избранных маршрутов
     */
    public function routes()
    {
        try {
            $routes = auth()->user()->favoriteRoutes()
                ->with(['countries', 'user'])
                ->withCount('favorites')
                ->paginate(12);

            return response()->json([
                'status' => 'success',
                'data' => $routes
            ]);
        } catch (\Exception $e) {
            Log::error('Error in FavoriteController@routes: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении списка избранных маршрутов'
            ], 500);
        }
    }

    /**
     * Добавление маршрута в избранное
     */
    public function addRoute(Route $route)
    {
        try {
            auth()->user()->favoriteRoutes()->attach($route->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Маршрут добавлен в избранное'
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
            auth()->user()->favoriteRoutes()->detach($route->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Маршрут удален из избранного'
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