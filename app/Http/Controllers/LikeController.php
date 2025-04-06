<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Recipe;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LikeController extends Controller
{
    /**
     * Лайк рецепта
     */
    public function likeRecipe(Recipe $recipe)
    {
        try {
            $user = auth()->user();

            if ($recipe->likes()->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Вы уже поставили лайк этому рецепту'
                ], 400);
            }

            $recipe->likes()->create([
                'user_id' => $user->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Лайк успешно поставлен'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in LikeController@likeRecipe: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при постановке лайка рецепту'
            ], 500);
        }
    }

    /**
     * Удаление лайка рецепта
     */
    public function unlikeRecipe(Recipe $recipe)
    {
        try {
            $user = auth()->user();

            $like = $recipe->likes()->where('user_id', $user->id)->first();

            if (!$like) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Вы еще не ставили лайк этому рецепту'
                ], 400);
            }

            $like->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Лайк успешно удален'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in LikeController@unlikeRecipe: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при удалении лайка рецепта'
            ], 500);
        }
    }

    /**
     * Лайк маршрута
     */
    public function likeRoute(Route $route)
    {
        try {
            $user = auth()->user();

            if ($route->likes()->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Вы уже поставили лайк этому маршруту'
                ], 400);
            }

            $route->likes()->create([
                'user_id' => $user->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Лайк успешно поставлен'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in LikeController@likeRoute: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при постановке лайка маршруту'
            ], 500);
        }
    }

    /**
     * Удаление лайка маршрута
     */
    public function unlikeRoute(Route $route)
    {
        try {
            $user = auth()->user();

            $like = $route->likes()->where('user_id', $user->id)->first();

            if (!$like) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Вы еще не ставили лайк этому маршруту'
                ], 400);
            }

            $like->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Лайк успешно удален'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in LikeController@unlikeRoute: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при удалении лайка маршрута'
            ], 500);
        }
    }

    /**
     * Лайк комментария
     */
    public function likeComment(Comment $comment)
    {
        try {
            $user = auth()->user();

            if ($comment->likes()->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Вы уже поставили лайк этому комментарию'
                ], 400);
            }

            $comment->likes()->create([
                'user_id' => $user->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Лайк успешно поставлен'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in LikeController@likeComment: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при постановке лайка комментарию'
            ], 500);
        }
    }

    /**
     * Удаление лайка комментария
     */
    public function unlikeComment(Comment $comment)
    {
        try {
            $user = auth()->user();

            $like = $comment->likes()->where('user_id', $user->id)->first();

            if (!$like) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Вы еще не ставили лайк этому комментарию'
                ], 400);
            }

            $like->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Лайк успешно удален'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in LikeController@unlikeComment: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при удалении лайка комментария'
            ], 500);
        }
    }

    public function toggleRecipe(Recipe $recipe)
    {
        try {
            $user = auth()->user();
            $isLiked = $recipe->toggleLike($user);

            return response()->json([
                'status' => 'success',
                'message' => $isLiked ? 'Рецепт добавлен в лайки' : 'Рецепт удален из лайков',
                'data' => [
                    'is_liked' => $isLiked,
                    'likes_count' => $recipe->likes()->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in LikeController@toggleRecipe: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при обработке лайка'
            ], 500);
        }
    }

    public function toggleRoute(Route $route)
    {
        try {
            $user = auth()->user();
            $isLiked = $route->toggleLike($user);

            return response()->json([
                'status' => 'success',
                'message' => $isLiked ? 'Маршрут добавлен в лайки' : 'Маршрут удален из лайков',
                'data' => [
                    'is_liked' => $isLiked,
                    'likes_count' => $route->likes()->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in LikeController@toggleRoute: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при обработке лайка'
            ], 500);
        }
    }
} 