<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    /**
     * Получение списка тегов
     */
    public function index(Request $request)
    {
        $query = Tag::query();
        
        // Поиск по названию
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $tags = $query->get();
        
        return response()->json($tags);
    }
    
    /**
     * Создание нового тега
     */
    public function store(Request $request)
    {
        // Проверка прав доступа
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'У вас нет прав на создание тегов'], 403);
        }
        
        // Валидация
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:tags,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            $tag = Tag::create([
                'name' => $request->name,
            ]);
            
            return response()->json($tag, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Не удалось создать тег: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Обновление тега
     */
    public function update(Request $request, $id)
    {
        // Проверка прав доступа
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'У вас нет прав на редактирование тегов'], 403);
        }
        
        $tag = Tag::findOrFail($id);
        
        // Валидация
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:tags,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            $tag->name = $request->name;
            $tag->save();
            
            return response()->json($tag);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Не удалось обновить тег: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Удаление тега
     */
    public function destroy($id)
    {
        // Проверка прав доступа
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'У вас нет прав на удаление тегов'], 403);
        }
        
        $tag = Tag::findOrFail($id);
        
        try {
            $tag->delete();
            return response()->json(['message' => 'Тег успешно удален']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Не удалось удалить тег: ' . $e->getMessage()], 500);
        }
    }
} 