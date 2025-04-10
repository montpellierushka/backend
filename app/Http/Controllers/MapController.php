<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    public function getRecipes(Request $request)
    {
        try {
            $query = Recipe::with(['country', 'tags', 'user'])
                ->select('id', 'title', 'country_id', 'latitude', 'longitude', 'cooking_time', 'user_id', 'created_at')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude');

            // Фильтрация по стране
            if ($request->has('country')) {
                $query->whereHas('country', function ($q) use ($request) {
                    $q->where('name', $request->country);
                });
            }

            // Фильтрация по тегам
            if ($request->has('tags')) {
                $tags = explode(',', $request->tags);
                $query->whereHas('tags', function ($q) use ($tags) {
                    $q->whereIn('name', $tags);
                });
            }

            // Фильтрация по времени приготовления
            if ($request->has('min_time')) {
                $query->where('cooking_time', '>=', $request->min_time);
            }
            if ($request->has('max_time')) {
                $query->where('cooking_time', '<=', $request->max_time);
            }

            // Поиск по названию
            if ($request->has('search')) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            $recipes = $query->get()
                ->map(function ($recipe) {
                    return [
                        'id' => $recipe->id,
                        'title' => $recipe->title,
                        'country' => $recipe->country->name,
                        'latitude' => $recipe->latitude,
                        'longitude' => $recipe->longitude,
                        'tags' => $recipe->tags->pluck('name')->toArray(),
                        'cooking_time' => $recipe->cooking_time,
                        'author' => $recipe->user->name,
                        'created_at' => $recipe->created_at->format('Y-m-d H:i:s')
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $recipes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка при получении рецептов для карты'
            ], 500);
        }
    }

    public function getFilters()
    {
        try {
            $countries = DB::table('recipes')
                ->join('countries', 'recipes.country_id', '=', 'countries.id')
                ->select('countries.id', 'countries.name', 'countries.code')
                ->distinct()
                ->get();

            $tags = DB::table('recipe_tag')
                ->join('tags', 'recipe_tag.tag_id', '=', 'tags.id')
                ->select('tags.id', 'tags.name', 'tags.slug')
                ->distinct()
                ->get();

            $cookingTimes = DB::table('recipes')
                ->select(DB::raw('MIN(cooking_time) as min_time'), DB::raw('MAX(cooking_time) as max_time'))
                ->first();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'countries' => $countries,
                    'tags' => $tags,
                    'cooking_times' => $cookingTimes
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка при получении фильтров'
            ], 500);
        }
    }

    public function getCountryStats()
    {
        try {
            $stats = Country::withCount(['recipes' => function($query) {
                $query->whereNotNull('latitude')
                      ->whereNotNull('longitude');
            }])
            ->get()
            ->map(function($country) {
                return [
                    'id' => $country->id,
                    'name' => $country->name,
                    'code' => $country->code,
                    'recipe_count' => $country->recipes_count,
                    'coordinates' => [
                        'latitude' => $country->latitude,
                        'longitude' => $country->longitude
                    ]
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка при получении статистики по странам'
            ], 500);
        }
    }

    public function getRecipeDetails($id)
    {
        try {
            $recipe = Recipe::with(['country', 'tags', 'user', 'comments.user'])
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $recipe->id,
                    'title' => $recipe->title,
                    'description' => $recipe->description,
                    'ingredients' => $recipe->ingredients,
                    'instructions' => $recipe->instructions,
                    'cooking_time' => $recipe->cooking_time,
                    'country' => $recipe->country->name,
                    'tags' => $recipe->tags->pluck('name')->toArray(),
                    'author' => $recipe->user->name,
                    'created_at' => $recipe->created_at->format('Y-m-d H:i:s'),
                    'comments' => $recipe->comments->map(function($comment) {
                        return [
                            'id' => $comment->id,
                            'text' => $comment->text,
                            'author' => $comment->user->name,
                            'created_at' => $comment->created_at->format('Y-m-d H:i:s')
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка при получении деталей рецепта'
            ], 500);
        }
    }
} 