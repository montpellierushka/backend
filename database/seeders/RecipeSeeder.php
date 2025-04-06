<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeStep;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        $countries = Country::all();
        $tags = Tag::all();

        $recipes = [
            [
                'title' => 'Паста Карбонара',
                'description' => 'Классическое итальянское блюдо с яйцами, сыром и беконом',
                'cooking_time' => 30,
                'servings' => 2,
                'country_id' => $countries->where('code', 'IT')->first()->id,
                'ingredients' => [
                    ['name' => 'Спагетти', 'amount' => 200, 'unit' => 'г', 'notes' => null],
                    ['name' => 'Бекон', 'amount' => 100, 'unit' => 'г', 'notes' => 'нарезанный кубиками'],
                    ['name' => 'Яйца', 'amount' => 2, 'unit' => 'шт', 'notes' => null],
                    ['name' => 'Пармезан', 'amount' => 50, 'unit' => 'г', 'notes' => 'тертый'],
                    ['name' => 'Черный перец', 'amount' => 1, 'unit' => 'ч.л.', 'notes' => 'свежемолотый'],
                    ['name' => 'Соль', 'amount' => 1, 'unit' => 'ч.л.', 'notes' => null],
                ],
                'steps' => [
                    [
                        'step_number' => 1,
                        'description' => 'Отварите спагетти в подсоленной воде до состояния аль денте.',
                        'image' => null
                    ],
                    [
                        'step_number' => 2,
                        'description' => 'Обжарьте бекон до хрустящей корочки.',
                        'image' => null
                    ],
                    [
                        'step_number' => 3,
                        'description' => 'Взбейте яйца с тертым пармезаном и черным перцем.',
                        'image' => null
                    ],
                    [
                        'step_number' => 4,
                        'description' => 'Смешайте спагетти с беконом и яичной смесью, быстро перемешайте.',
                        'image' => null
                    ]
                ],
                'tags' => ['Основные блюда']
            ],
            [
                'title' => 'Рамен',
                'description' => 'Японский суп с лапшой, бульоном и различными начинками',
                'cooking_time' => 45,
                'servings' => 2,
                'country_id' => $countries->where('code', 'JP')->first()->id,
                'ingredients' => [
                    ['name' => 'Лапша рамен', 'amount' => 200, 'unit' => 'г', 'notes' => null],
                    ['name' => 'Куриный бульон', 'amount' => 1, 'unit' => 'л', 'notes' => null],
                    ['name' => 'Свинина', 'amount' => 200, 'unit' => 'г', 'notes' => 'нарезанная тонкими ломтиками'],
                    ['name' => 'Яйца', 'amount' => 2, 'unit' => 'шт', 'notes' => 'всмятку'],
                    ['name' => 'Зеленый лук', 'amount' => 2, 'unit' => 'шт', 'notes' => 'нарезанный'],
                    ['name' => 'Морские водоросли', 'amount' => 10, 'unit' => 'г', 'notes' => null],
                ],
                'steps' => [
                    [
                        'step_number' => 1,
                        'description' => 'Приготовьте куриный бульон.',
                        'image' => null
                    ],
                    [
                        'step_number' => 2,
                        'description' => 'Отварите лапшу рамен.',
                        'image' => null
                    ],
                    [
                        'step_number' => 3,
                        'description' => 'Обжарьте свинину.',
                        'image' => null
                    ],
                    [
                        'step_number' => 4,
                        'description' => 'Сварите яйца всмятку.',
                        'image' => null
                    ],
                    [
                        'step_number' => 5,
                        'description' => 'Соберите рамен: в миску положите лапшу, залейте бульоном, добавьте свинину, яйца, зеленый лук и водоросли.',
                        'image' => null
                    ]
                ],
                'tags' => ['Супы']
            ]
        ];

        foreach ($recipes as $recipeData) {
            $recipe = Recipe::create([
                'title' => $recipeData['title'],
                'description' => $recipeData['description'],
                'cooking_time' => $recipeData['cooking_time'],
                'servings' => $recipeData['servings'],
                'country_id' => $recipeData['country_id'],
                'user_id' => $user->id,
                'image' => null
            ]);

            foreach ($recipeData['ingredients'] as $ingredient) {
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'name' => $ingredient['name'],
                    'amount' => $ingredient['amount'],
                    'unit' => $ingredient['unit'],
                    'notes' => $ingredient['notes']
                ]);
            }

            foreach ($recipeData['steps'] as $step) {
                RecipeStep::create([
                    'recipe_id' => $recipe->id,
                    'step_number' => $step['step_number'],
                    'description' => $step['description'],
                    'image' => $step['image']
                ]);
            }

            foreach ($recipeData['tags'] as $tagName) {
                $tag = $tags->where('name', $tagName)->first();
                if ($tag) {
                    $recipe->tags()->attach($tag->id);
                }
            }
        }
    }
} 