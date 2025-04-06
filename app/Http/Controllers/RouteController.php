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
     * Получение списка маршрутов
     */
    public function index(Request $request)
    {
        try {
            $query = Route::query()
                ->with(['countries', 'user'])
                ->when($request->user_id, function ($q) use ($request) {
                    return $q->where('user_id', $request->user_id);
                });

            $routes = $query->paginate($request->per_page ?? 12);

            return response()->json([
                'success' => true,
                'data' => $routes
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting routes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Получение детальной информации о маршруте
     */
    public function show(Route $route)
    {
        try {
            $route->load(['countries', 'user']);

            return response()->json([
                'success' => true,
                'data' => $route
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting route: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Создание нового маршрута
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $route = Route::create([
                'title' => $request->title,
                'description' => $request->description,
                'duration' => $request->duration,
                'user_id' => $request->user()->id,
            ]);

            if ($request->countries) {
                $route->countries()->attach($request->countries);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $route->load(['countries', 'user'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating route: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Обновление маршрута
     */
    public function update(Request $request, Route $route)
    {
        try {
            DB::beginTransaction();

            $route->update([
                'title' => $request->title,
                'description' => $request->description,
                'duration' => $request->duration,
            ]);

            if ($request->countries) {
                $route->countries()->sync($request->countries);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $route->load(['countries', 'user'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating route: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Удаление маршрута
     */
    public function destroy(Route $route)
    {
        try {
            $route->delete();

            return response()->json([
                'success' => true,
                'message' => 'Route deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting route: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
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