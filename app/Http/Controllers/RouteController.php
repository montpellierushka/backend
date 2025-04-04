<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RouteController extends Controller
{
    /**
     * Получение списка маршрутов
     */
    public function index(Request $request)
    {
        $query = Route::with(['user', 'recipes']);
        
        // Фильтр по названию
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        // Фильтр по стране
        if ($request->has('country')) {
            $query->where('country', $request->country);
        }
        
        // Пагинация
        $routes = $query->paginate(10);
        
        return response()->json($routes);
    }
    
    /**
     * Получение конкретного маршрута
     */
    public function show($id)
    {
        $route = Route::with(['user', 'recipes'])->findOrFail($id);
        return response()->json($route);
    }
    
    /**
     * Создание нового маршрута
     */
    public function store(Request $request)
    {
        // Валидация
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'country' => 'required|string|max:100',
            'duration' => 'required|integer|min:1',
            'image' => 'nullable|image|max:2048',
            'recipes' => 'required|array',
            'recipes.*.id' => 'required|exists:recipes,id',
            'recipes.*.order' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Начинаем транзакцию
        DB::beginTransaction();
        
        try {
            // Создаем маршрут
            $route = new Route();
            $route->name = $request->name;
            $route->description = $request->description;
            $route->country = $request->country;
            $route->duration = $request->duration;
            $route->user_id = Auth::id();
            
            // Обработка изображения
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('routes', 'public');
                $route->image = $path;
            }
            
            $route->save();
            
            // Добавляем рецепты с порядком
            $recipesData = [];
            foreach ($request->recipes as $recipeData) {
                $recipesData[$recipeData['id']] = ['order' => $recipeData['order']];
            }
            
            $route->recipes()->sync($recipesData);
            
            DB::commit();
            
            return response()->json($route->load('recipes'), 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Не удалось создать маршрут: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Обновление маршрута
     */
    public function update(Request $request, $id)
    {
        $route = Route::findOrFail($id);
        
        // Проверка прав доступа
        if ($route->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'У вас нет прав на редактирование этого маршрута'], 403);
        }
        
        // Валидация
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'string',
            'country' => 'string|max:100',
            'duration' => 'integer|min:1',
            'image' => 'nullable|image|max:2048',
            'recipes' => 'array',
            'recipes.*.id' => 'exists:recipes,id',
            'recipes.*.order' => 'integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Начинаем транзакцию
        DB::beginTransaction();
        
        try {
            // Обновляем данные
            if ($request->has('name')) $route->name = $request->name;
            if ($request->has('description')) $route->description = $request->description;
            if ($request->has('country')) $route->country = $request->country;
            if ($request->has('duration')) $route->duration = $request->duration;
            
            // Обработка изображения
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('routes', 'public');
                $route->image = $path;
            }
            
            $route->save();
            
            // Обновляем рецепты
            if ($request->has('recipes')) {
                $recipesData = [];
                foreach ($request->recipes as $recipeData) {
                    $recipesData[$recipeData['id']] = ['order' => $recipeData['order']];
                }
                $route->recipes()->sync($recipesData);
            }
            
            DB::commit();
            
            return response()->json($route->load('recipes'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Не удалось обновить маршрут: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Удаление маршрута
     */
    public function destroy($id)
    {
        $route = Route::findOrFail($id);
        
        // Проверка прав доступа
        if ($route->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'У вас нет прав на удаление этого маршрута'], 403);
        }
        
        try {
            $route->delete();
            return response()->json(['message' => 'Маршрут успешно удален']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Не удалось удалить маршрут: ' . $e->getMessage()], 500);
        }
    }
} 