<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * Загрузка изображения
     */
    public function image(Request $request)
    {
        try {
            $validator = $request->validate([
                'image' => 'required|image|max:2048',
                'type' => 'required|string|in:recipe,recipe-step,avatar,flag'
            ]);

            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = Str::random(40) . '.' . $extension;

            $path = $file->storeAs(
                $validator['type'] . 's',
                $filename,
                'public'
            );

            return response()->json([
                'status' => 'success',
                'data' => [
                    'path' => $path,
                    'url' => Storage::disk('public')->url($path)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in UploadController@image: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при загрузке изображения'
            ], 500);
        }
    }

    /**
     * Удаление файла
     */
    public function delete(Request $request)
    {
        try {
            $validator = $request->validate([
                'path' => 'required|string'
            ]);

            if (Storage::disk('public')->exists($validator['path'])) {
                Storage::disk('public')->delete($validator['path']);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Файл успешно удален'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in UploadController@delete: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при удалении файла'
            ], 500);
        }
    }
} 