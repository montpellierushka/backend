<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Recipe;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    /**
     * Получение комментариев рецепта
     */
    public function recipeComments(Recipe $recipe)
    {
        try {
            $comments = $recipe->comments()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'status' => 'success',
                'data' => $comments
            ]);
        } catch (\Exception $e) {
            Log::error('Error in CommentController@recipeComments: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении комментариев рецепта'
            ], 500);
        }
    }

    /**
     * Добавление комментария к рецепту
     */
    public function addRecipeComment(Request $request, Recipe $recipe)
    {
        try {
            $validated = $request->validate([
                'text' => 'required|string|max:1000'
            ]);

            $comment = $recipe->comments()->create([
                'user_id' => auth()->id(),
                'text' => $validated['text']
            ]);

            $comment->load('user');

            return response()->json([
                'status' => 'success',
                'data' => $comment
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error in CommentController@addRecipeComment: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при добавлении комментария к рецепту'
            ], 500);
        }
    }

    /**
     * Получение комментариев маршрута
     */
    public function routeComments(Route $route)
    {
        try {
            $comments = $route->comments()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'status' => 'success',
                'data' => $comments
            ]);
        } catch (\Exception $e) {
            Log::error('Error in CommentController@routeComments: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении комментариев маршрута'
            ], 500);
        }
    }

    /**
     * Добавление комментария к маршруту
     */
    public function addRouteComment(Request $request, Route $route)
    {
        try {
            $validated = $request->validate([
                'text' => 'required|string|max:1000'
            ]);

            $comment = $route->comments()->create([
                'user_id' => auth()->id(),
                'text' => $validated['text']
            ]);

            $comment->load('user');

            return response()->json([
                'status' => 'success',
                'data' => $comment
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error in CommentController@addRouteComment: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при добавлении комментария к маршруту'
            ], 500);
        }
    }

    /**
     * Удаление комментария
     */
    public function destroy(Comment $comment)
    {
        try {
            if ($comment->user_id !== auth()->id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'У вас нет прав на удаление этого комментария'
                ], 403);
            }

            $comment->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Комментарий успешно удален'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in CommentController@destroy: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при удалении комментария'
            ], 500);
        }
    }
} 