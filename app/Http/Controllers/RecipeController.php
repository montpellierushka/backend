<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RecipeController extends Controller
{
    /**
     * Получение списка рецептов с возможностью фильтрации
     */
    public function index(Request $request)
    {
        $query = Recipe::with(['user', 'tags']);
        
        // Фильтр по стране
        if ($request->has('country')) {
            $query->where('country', $request->country);
        }
        
        // Фильтр по тегу
        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('name', $request->tag);
            });
        }
        
        // Поиск по названию или ингредиентам
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('ingredients', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Пагинация
        $recipes = $query->paginate(10);
        
        return response()->json($recipes);
    }
    
    /**
     * Получение конкретного рецепта
     */
    public function show($id)
    {
        $recipe = Recipe::with(['user', 'tags'])->findOrFail($id);
        return response()->json($recipe);
    }
    
    /**
     * Создание нового рецепта
     */
    public function store(Request $request)
    {
        // Валидация
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'ingredients' => 'required|string',
            'instructions' => 'required|string',
            'cooking_time' => 'required|integer|min:1',
            'country' => 'required|string|max:100',
            'image' => 'nullable|image|max:2048',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Начинаем транзакцию
        DB::beginTransaction();
        
        try {
            // Создаем рецепт
            $recipe = new Recipe();
            $recipe->title = $request->title;
            $recipe->description = $request->description;
            $recipe->ingredients = $request->ingredients;
            $recipe->instructions = $request->instructions;
            $recipe->cooking_time = $request->cooking_time;
            $recipe->country = $request->country;
            $recipe->user_id = Auth::id();
            
            // Обработка изображения
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('recipes', 'public');
                $recipe->image = $path;
            }
            
            $recipe->save();
            
            // Добавляем теги
            if ($request->has('tags')) {
                $recipe->tags()->sync($request->tags);
            }
            
            DB::commit();
            
            return response()->json($recipe->load('tags'), 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Не удалось создать рецепт: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Обновление рецепта
     */
    public function update(Request $request, $id)
    {
        $recipe = Recipe::findOrFail($id);
        
        // Проверка прав доступа
        if ($recipe->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'У вас нет прав на редактирование этого рецепта'], 403);
        }
        
        // Валидация
        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'description' => 'string',
            'ingredients' => 'string',
            'instructions' => 'string',
            'cooking_time' => 'integer|min:1',
            'country' => 'string|max:100',
            'image' => 'nullable|image|max:2048',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Начинаем транзакцию
        DB::beginTransaction();
        
        try {
            // Обновляем данные
            if ($request->has('title')) $recipe->title = $request->title;
            if ($request->has('description')) $recipe->description = $request->description;
            if ($request->has('ingredients')) $recipe->ingredients = $request->ingredients;
            if ($request->has('instructions')) $recipe->instructions = $request->instructions;
            if ($request->has('cooking_time')) $recipe->cooking_time = $request->cooking_time;
            if ($request->has('country')) $recipe->country = $request->country;
            
            // Обработка изображения
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('recipes', 'public');
                $recipe->image = $path;
            }
            
            $recipe->save();
            
            // Обновляем теги
            if ($request->has('tags')) {
                $recipe->tags()->sync($request->tags);
            }
            
            DB::commit();
            
            return response()->json($recipe->load('tags'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Не удалось обновить рецепт: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Удаление рецепта
     */
    public function destroy($id)
    {
        $recipe = Recipe::findOrFail($id);
        
        // Проверка прав доступа
        if ($recipe->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'У вас нет прав на удаление этого рецепта'], 403);
        }
        
        try {
            $recipe->delete();
            return response()->json(['message' => 'Рецепт успешно удален']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Не удалось удалить рецепт: ' . $e->getMessage()], 500);
        }
    }
} 