<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RouteController extends Controller
{
    /**
     * Получение списка маршрутов с фильтрацией
     */
    public function index(Request $request)
    {
        try {
            $query = Route::with(['countries', 'user'])
                ->withCount('favorites');

            // Фильтрация по странам
            if ($request->has('countries')) {
                $countries = explode(',', $request->countries);
                $query->whereHas('countries', function ($q) use ($countries) {
                    $q->whereIn('countries.id', $countries);
                });
            }

            // Фильтрация по длительности
            if ($request->has('duration')) {
                $query->where('duration', '<=', $request->duration);
            }

            // Сортировка
            $sort = $request->get('sort', 'created_at');
            $direction = $request->get('direction', 'desc');
            $query->orderBy($sort, $direction);

            $routes = $query->paginate(12);

            return response()->json([
                'status' => 'success',
                'data' => $routes
            ]);
        } catch (\Exception $e) {
            Log::error('Error in RouteController@index: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении списка маршрутов'
            ], 500);
        }
    }

    /**
     * Получение информации о маршруте
     */
    public function show(Route $route)
    {
        try {
            $route->load(['countries', 'user'])
                ->loadCount('favorites');

            return response()->json([
                'status' => 'success',
                'data' => $route
            ]);
        } catch (\Exception $e) {
            Log::error('Error in RouteController@show: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении информации о маршруте'
            ], 500);
        }
    }

    /**
     * Создание нового маршрута
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'nullable|image|max:2048',
                'countries' => 'required|array',
                'countries.*.id' => 'required|exists:countries,id',
                'countries.*.order' => 'required|integer|min:0'
            ]);

            DB::beginTransaction();

            $route = Route::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'user_id' => auth()->id()
            ]);

            // Привязка стран с порядком
            foreach ($validated['countries'] as $country) {
                $route->countries()->attach($country['id'], ['order' => $country['order']]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $route->load('countries')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in RouteController@store: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при создании маршрута'
            ], 500);
        }
    }

    /**
     * Обновление маршрута
     */
    public function update(Request $request, Route $route)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'nullable|image|max:2048',
                'countries' => 'required|array',
                'countries.*.id' => 'required|exists:countries,id',
                'countries.*.order' => 'required|integer|min:0'
            ]);

            DB::beginTransaction();

            $route->update([
                'name' => $validated['name'],
                'description' => $validated['description']
            ]);

            // Обновление привязки стран с порядком
            $route->countries()->detach();
            foreach ($validated['countries'] as $country) {
                $route->countries()->attach($country['id'], ['order' => $country['order']]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $route->load('countries')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in RouteController@update: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при обновлении маршрута'
            ], 500);
        }
    }

    /**
     * Удаление маршрута
     */
    public function destroy(Route $route)
    {
        try {
            DB::beginTransaction();

            $route->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Маршрут успешно удален'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in RouteController@destroy: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при удалении маршрута'
            ], 500);
        }
    }

    /**
     * Получение списка стран
     */
    public function countries()
    {
        try {
            $countries = Country::all();

            return response()->json([
                'success' => true,
                'data' => $countries
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting countries: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
} 