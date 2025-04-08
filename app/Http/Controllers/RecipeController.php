<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeStep;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    /**
     * Получение списка рецептов с фильтрацией
     */
    public function index(Request $request)
    {
        try {
            $query = Recipe::with(['country', 'tags', 'user'])
                ->withCount('favoritedBy as favorites_count');

            // Фильтрация по стране
            if ($request->has('country_id')) {
                $query->where('country_id', $request->country_id);
            }

            // Фильтрация по тегам
            if ($request->has('tags')) {
                $tags = explode(',', $request->tags);
                $query->whereHas('tags', function ($q) use ($tags) {
                    $q->whereIn('tags.id', $tags);
                });
            }

            // Фильтрация по времени приготовления
            if ($request->has('cooking_time')) {
                $query->where('cooking_time', '<=', $request->cooking_time);
            }

            // Сортировка
            $sort = $request->get('sort', 'created_at');
            $direction = $request->get('direction', 'desc');
            $query->orderBy($sort, $direction);

            $recipes = $query->paginate(12);

            return response()->json([
                'status' => 'success',
                'data' => $recipes
            ]);
        } catch (\Exception $e) {
            Log::error('Error in RecipeController@index: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении списка рецептов'
            ], 500);
        }
    }

    /**
     * Получение информации о рецепте
     */
    public function show(Recipe $recipe)
    {
        try {
            $recipe->load([
                'country',
                'tags',
                'ingredients',
                'steps',
                'user'
            ])->loadCount('favoritedBy as favorites_count');

            return response()->json([
                'status' => 'success',
                'data' => $recipe
            ]);
        } catch (\Exception $e) {
            Log::error('Error in RecipeController@show: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении информации о рецепте'
            ], 500);
        }
    }

    /**
     * Создание нового рецепта
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'cooking_time' => 'required|integer|min:1',
                'servings' => 'required|integer|min:1',
                'country_id' => 'required|exists:countries,id',
                'difficulty' => 'required|in:easy,medium,hard',
                'ingredients' => 'required|array',
                'ingredients.*.name' => 'required|string',
                'ingredients.*.amount' => 'required|numeric',
                'ingredients.*.unit' => 'required|string',
                'steps' => 'required|array',
                'steps.*.description' => 'required|string',
                'steps.*.image' => 'nullable|image|max:2048',
                'image' => 'nullable|image|max:2048',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:tags,id'
            ]);

            // Создаем рецепт
            $recipe = Recipe::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'cooking_time' => $validated['cooking_time'],
                'servings' => $validated['servings'],
                'country_id' => $validated['country_id'],
                'difficulty' => $validated['difficulty']
            ]);

            // Загружаем основное изображение
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('recipes', 'public');
                $recipe->image = $imagePath;
                $recipe->save();
            }

            // Создаем ингредиенты
            foreach ($validated['ingredients'] as $ingredient) {
                $recipe->ingredients()->create([
                    'name' => $ingredient['name'],
                    'amount' => $ingredient['amount'],
                    'unit' => $ingredient['unit']
                ]);
            }

            // Создаем шаги
            foreach ($validated['steps'] as $index => $step) {
                $stepData = [
                    'description' => $step['description'],
                    'step_number' => $index + 1
                ];

                if (isset($step['image']) && $request->hasFile("steps.{$index}.image")) {
                    $imagePath = $request->file("steps.{$index}.image")->store('recipe-steps', 'public');
                    $stepData['image'] = $imagePath;
                }

                $recipe->steps()->create($stepData);
            }

            // Привязываем теги
            if (!empty($validated['tags'])) {
                $recipe->tags()->attach($validated['tags']);
            }

            return response()->json([
                'message' => 'Рецепт успешно создан',
                'recipe' => $recipe->load(['ingredients', 'steps', 'tags', 'country'])
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Ошибка при создании рецепта: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка при создании рецепта',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обновление рецепта
     */
    public function update(Request $request, Recipe $recipe)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'cooking_time' => 'required|integer|min:1',
                'servings' => 'required|integer|min:1',
                'country_id' => 'required|exists:countries,id',
                'image' => 'nullable|image|max:2048',
                'ingredients' => 'required|array',
                'ingredients.*.name' => 'required|string',
                'ingredients.*.amount' => 'required|numeric',
                'ingredients.*.unit' => 'required|string',
                'ingredients.*.notes' => 'nullable|string',
                'steps' => 'required|array',
                'steps.*.description' => 'required|string',
                'steps.*.image' => 'nullable|image|max:2048',
                'tags' => 'required|array',
                'tags.*' => 'exists:tags,id'
            ]);

            \DB::beginTransaction();

            // Обновление рецепта
            $recipe->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'cooking_time' => $validated['cooking_time'],
                'servings' => $validated['servings'],
                'country_id' => $validated['country_id']
            ]);

            // Обновление изображения рецепта
            if ($request->hasFile('image')) {
                if ($recipe->image) {
                    Storage::disk('public')->delete($recipe->image);
                }
                $path = $request->file('image')->store('recipes', 'public');
                $recipe->image = $path;
                $recipe->save();
            }

            // Обновление ингредиентов
            $recipe->ingredients()->delete();
            foreach ($validated['ingredients'] as $ingredient) {
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'name' => $ingredient['name'],
                    'amount' => $ingredient['amount'],
                    'unit' => $ingredient['unit'],
                    'notes' => $ingredient['notes'] ?? null
                ]);
            }

            // Обновление шагов
            $recipe->steps()->delete();
            foreach ($validated['steps'] as $index => $step) {
                $stepData = [
                    'recipe_id' => $recipe->id,
                    'step_number' => $index + 1,
                    'description' => $step['description']
                ];

                if (isset($step['image'])) {
                    $path = $step['image']->store('recipe-steps', 'public');
                    $stepData['image'] = $path;
                }

                RecipeStep::create($stepData);
            }

            // Обновление тегов
            $recipe->tags()->sync($validated['tags']);

            \DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $recipe->load(['country', 'tags', 'ingredients', 'steps'])
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error in RecipeController@update: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при обновлении рецепта'
            ], 500);
        }
    }

    /**
     * Удаление рецепта
     */
    public function destroy(Recipe $recipe)
    {
        try {
            \DB::beginTransaction();

            // Удаление изображений
            if ($recipe->image) {
                Storage::disk('public')->delete($recipe->image);
            }
            foreach ($recipe->steps as $step) {
                if ($step->image) {
                    Storage::disk('public')->delete($step->image);
                }
            }

            $recipe->delete();

            \DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Рецепт успешно удален'
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error in RecipeController@destroy: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при удалении рецепта'
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
                'status' => 'success',
                'data' => $tags
            ]);
        } catch (\Exception $e) {
            Log::error('Error in RecipeController@tags: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении списка тегов'
            ], 500);
        }
    }
} 