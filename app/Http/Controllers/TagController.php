<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TagController extends Controller
{
    /**
     * Получение списка тегов
     */
    public function index()
    {
        try {
            $tags = Tag::all();

            return response()->json([
                'status' => 'success',
                'data' => $tags
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TagController@index: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении списка тегов'
            ], 500);
        }
    }

    /**
     * Получение информации о теге
     */
    public function show(Tag $tag)
    {
        try {
            $tag->load('recipes');

            return response()->json([
                'status' => 'success',
                'data' => $tag
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TagController@show: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении информации о теге'
            ], 500);
        }
    }

    /**
     * Создание нового тега
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:tags',
                'slug' => 'required|string|max:255|unique:tags',
                'description' => 'required|string'
            ]);

            $tag = Tag::create($validated);

            return response()->json([
                'status' => 'success',
                'data' => $tag
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error in TagController@store: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при создании тега'
            ], 500);
        }
    }

    /**
     * Обновление тега
     */
    public function update(Request $request, Tag $tag)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:tags,name,' . $tag->id,
                'slug' => 'required|string|max:255|unique:tags,slug,' . $tag->id,
                'description' => 'required|string'
            ]);

            $tag->update($validated);

            return response()->json([
                'status' => 'success',
                'data' => $tag
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TagController@update: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при обновлении тега'
            ], 500);
        }
    }

    /**
     * Удаление тега
     */
    public function destroy(Tag $tag)
    {
        try {
            $tag->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Тег успешно удален'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TagController@destroy: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при удалении тега'
            ], 500);
        }
    }
} 