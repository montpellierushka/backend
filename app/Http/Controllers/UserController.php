<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Получение информации о текущем пользователе
     */
    public function me()
    {
        try {
            $user = auth()->user()->load(['recipes', 'routes']);

            return response()->json([
                'status' => 'success',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Error in UserController@me: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении информации о пользователе'
            ], 500);
        }
    }

    /**
     * Обновление информации о пользователе
     */
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
                'avatar' => 'nullable|image|max:2048',
                'password' => 'nullable|string|min:8|confirmed'
            ]);

            $user = auth()->user();
            $data = [
                'name' => $validated['name'],
                'email' => $validated['email']
            ];

            if ($request->hasFile('avatar')) {
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $path = $request->file('avatar')->store('avatars', 'public');
                $data['avatar'] = $path;
            }

            if ($request->has('password')) {
                $data['password'] = Hash::make($validated['password']);
            }

            $user->update($data);

            return response()->json([
                'status' => 'success',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Error in UserController@update: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при обновлении информации о пользователе'
            ], 500);
        }
    }

    /**
     * Получение списка рецептов пользователя
     */
    public function recipes()
    {
        try {
            $recipes = auth()->user()->recipes()
                ->with(['country', 'tags'])
                ->withCount('favorites')
                ->paginate(12);

            return response()->json([
                'status' => 'success',
                'data' => $recipes
            ]);
        } catch (\Exception $e) {
            Log::error('Error in UserController@recipes: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении списка рецептов пользователя'
            ], 500);
        }
    }

    /**
     * Получение списка маршрутов пользователя
     */
    public function routes()
    {
        try {
            $routes = auth()->user()->routes()
                ->with(['countries'])
                ->withCount('favorites')
                ->paginate(12);

            return response()->json([
                'status' => 'success',
                'data' => $routes
            ]);
        } catch (\Exception $e) {
            Log::error('Error in UserController@routes: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении списка маршрутов пользователя'
            ], 500);
        }
    }
} 