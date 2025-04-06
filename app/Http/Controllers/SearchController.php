<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    /**
     * Поиск рецептов
     */
    public function recipes(Request $request)
    {
        try {
            $query = Recipe::query()
                ->with(['country', 'tags', 'user'])
                ->withCount('favorites');

            if ($request->has('q')) {
                $searchTerm = $request->q;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                        ->orWhere('description', 'like', "%{$searchTerm}%");
                });
            }

            if ($request->has('country_id')) {
                $query->where('country_id', $request->country_id);
            }

            if ($request->has('tags')) {
                $tags = explode(',', $request->tags);
                $query->whereHas('tags', function ($q) use ($tags) {
                    $q->whereIn('tags.id', $tags);
                });
            }

            if ($request->has('cooking_time')) {
                $query->where('cooking_time', '<=', $request->cooking_time);
            }

            $sort = $request->get('sort', 'created_at');
            $direction = $request->get('direction', 'desc');
            $query->orderBy($sort, $direction);

            $recipes = $query->paginate(12);

            return response()->json([
                'status' => 'success',
                'data' => $recipes
            ]);
        } catch (\Exception $e) {
            Log::error('Error in SearchController@recipes: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при поиске рецептов'
            ], 500);
        }
    }

    /**
     * Поиск маршрутов
     */
    public function routes(Request $request)
    {
        try {
            $query = Route::query()
                ->with(['countries', 'user'])
                ->withCount('favorites');

            if ($request->has('q')) {
                $searchTerm = $request->q;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                        ->orWhere('description', 'like', "%{$searchTerm}%");
                });
            }

            if ($request->has('countries')) {
                $countries = explode(',', $request->countries);
                $query->whereHas('countries', function ($q) use ($countries) {
                    $q->whereIn('countries.id', $countries);
                });
            }

            if ($request->has('duration')) {
                $query->where('duration', '<=', $request->duration);
            }

            $sort = $request->get('sort', 'created_at');
            $direction = $request->get('direction', 'desc');
            $query->orderBy($sort, $direction);

            $routes = $query->paginate(12);

            return response()->json([
                'status' => 'success',
                'data' => $routes
            ]);
        } catch (\Exception $e) {
            Log::error('Error in SearchController@routes: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при поиске маршрутов'
            ], 500);
        }
    }

    /**
     * Глобальный поиск
     */
    public function global(Request $request)
    {
        try {
            if (!$request->has('q')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Параметр поиска обязателен'
                ], 422);
            }

            $searchTerm = $request->q;

            $recipes = Recipe::where('title', 'like', "%{$searchTerm}%")
                ->orWhere('description', 'like', "%{$searchTerm}%")
                ->with(['country', 'tags'])
                ->withCount('favorites')
                ->take(5)
                ->get();

            $routes = Route::where('title', 'like', "%{$searchTerm}%")
                ->orWhere('description', 'like', "%{$searchTerm}%")
                ->with(['countries'])
                ->withCount('favorites')
                ->take(5)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'recipes' => $recipes,
                    'routes' => $routes
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in SearchController@global: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при глобальном поиске'
            ], 500);
        }
    }
} 