<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Recipe;
use App\Models\Route;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StatsController extends Controller
{
    /**
     * Получение общей статистики
     */
    public function index()
    {
        try {
            $stats = [
                'users' => User::count(),
                'recipes' => Recipe::count(),
                'routes' => Route::count(),
                'countries' => Country::count(),
                'popular_countries' => Country::withCount('recipes')
                    ->orderBy('recipes_count', 'desc')
                    ->take(5)
                    ->get(),
                'popular_recipes' => Recipe::withCount('favorites')
                    ->orderBy('favorites_count', 'desc')
                    ->take(5)
                    ->get(),
                'popular_routes' => Route::withCount('favorites')
                    ->orderBy('favorites_count', 'desc')
                    ->take(5)
                    ->get()
            ];

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error in StatsController@index: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении статистики'
            ], 500);
        }
    }

    /**
     * Получение статистики по странам
     */
    public function countries()
    {
        try {
            $stats = Country::withCount(['recipes', 'routes'])
                ->orderBy('recipes_count', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error in StatsController@countries: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении статистики по странам'
            ], 500);
        }
    }

    /**
     * Получение статистики по рецептам
     */
    public function recipes()
    {
        try {
            $stats = [
                'total' => Recipe::count(),
                'by_country' => Country::withCount('recipes')
                    ->orderBy('recipes_count', 'desc')
                    ->get(),
                'by_user' => User::withCount('recipes')
                    ->orderBy('recipes_count', 'desc')
                    ->take(10)
                    ->get(),
                'popular' => Recipe::withCount('favorites')
                    ->orderBy('favorites_count', 'desc')
                    ->take(10)
                    ->get()
            ];

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error in StatsController@recipes: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении статистики по рецептам'
            ], 500);
        }
    }

    /**
     * Получение статистики по маршрутам
     */
    public function routes()
    {
        try {
            $stats = [
                'total' => Route::count(),
                'by_country' => Country::withCount('routes')
                    ->orderBy('routes_count', 'desc')
                    ->get(),
                'by_user' => User::withCount('routes')
                    ->orderBy('routes_count', 'desc')
                    ->take(10)
                    ->get(),
                'popular' => Route::withCount('favorites')
                    ->orderBy('favorites_count', 'desc')
                    ->take(10)
                    ->get()
            ];

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error in StatsController@routes: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении статистики по маршрутам'
            ], 500);
        }
    }
} 