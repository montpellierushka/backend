<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    /**
     * Получение списка рецептов с фильтрацией
     */
    public function index(Request $request)
    {
        try {
            $query = Recipe::query()
                ->with(['tags', 'country'])
                ->when($request->country, function ($q) use ($request) {
                    return $q->whereHas('country', function ($q) use ($request) {
                        $q->where('code', $request->country);
                    });
                })
                ->when($request->tags, function ($q) use ($request) {
                    return $q->whereHas('tags', function ($q) use ($request) {
                        $q->whereIn('id', explode(',', $request->tags));
                    });
                })
                ->when($request->cooking_time, function ($q) use ($request) {
                    return $q->where('cooking_time', '<=', $request->cooking_time);
                });

            $recipes = $query->paginate($request->per_page ?? 12);

            return response()->json([
                'success' => true,
                'data' => $recipes
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting recipes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Получение детальной информации о рецепте
     */
    public function show(Recipe $recipe)
    {
        try {
            $recipe->load(['tags', 'country', 'ingredients', 'steps']);

            return response()->json([
                'success' => true,
                'data' => $recipe
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting recipe: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Создание нового рецепта
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $recipe = Recipe::create([
                'title' => $request->title,
                'description' => $request->description,
                'cooking_time' => $request->cooking_time,
                'servings' => $request->servings,
                'country_id' => $request->country_id,
                'image' => $request->image,
            ]);

            if ($request->tags) {
                $recipe->tags()->attach($request->tags);
            }

            if ($request->ingredients) {
                $recipe->ingredients()->createMany($request->ingredients);
            }

            if ($request->steps) {
                $recipe->steps()->createMany($request->steps);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $recipe->load(['tags', 'country', 'ingredients', 'steps'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating recipe: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Обновление рецепта
     */
    public function update(Request $request, Recipe $recipe)
    {
        try {
            DB::beginTransaction();

            $recipe->update([
                'title' => $request->title,
                'description' => $request->description,
                'cooking_time' => $request->cooking_time,
                'servings' => $request->servings,
                'country_id' => $request->country_id,
                'image' => $request->image,
            ]);

            if ($request->tags) {
                $recipe->tags()->sync($request->tags);
            }

            if ($request->ingredients) {
                $recipe->ingredients()->delete();
                $recipe->ingredients()->createMany($request->ingredients);
            }

            if ($request->steps) {
                $recipe->steps()->delete();
                $recipe->steps()->createMany($request->steps);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $recipe->load(['tags', 'country', 'ingredients', 'steps'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating recipe: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Удаление рецепта
     */
    public function destroy(Recipe $recipe)
    {
        try {
            $recipe->delete();

            return response()->json([
                'success' => true,
                'message' => 'Recipe deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting recipe: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Получение списка тегов
     */
    public function tags()
    {
        try {
            $tags = Tag::all();

            return response()->json([
                'success' => true,
                'data' => $tags
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting tags: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
} 