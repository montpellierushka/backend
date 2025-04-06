<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            [
                'name' => 'Вегетарианское',
                'description' => 'Блюда без мяса и рыбы'
            ],
            [
                'name' => 'Веганское',
                'description' => 'Блюда без продуктов животного происхождения'
            ],
            [
                'name' => 'Без глютена',
                'description' => 'Блюда без глютена'
            ],
            [
                'name' => 'Без лактозы',
                'description' => 'Блюда без молочных продуктов'
            ],
            [
                'name' => 'Острое',
                'description' => 'Блюда с острыми специями'
            ],
            [
                'name' => 'Десерты',
                'description' => 'Сладкие блюда и выпечка'
            ],
            [
                'name' => 'Супы',
                'description' => 'Первые блюда'
            ],
            [
                'name' => 'Салаты',
                'description' => 'Холодные и теплые салаты'
            ],
            [
                'name' => 'Основные блюда',
                'description' => 'Основные горячие блюда'
            ],
            [
                'name' => 'Закуски',
                'description' => 'Легкие закуски и аперитивы'
            ]
        ];

        foreach ($tags as $tag) {
            $tag['slug'] = Str::slug($tag['name']);
            Tag::create($tag);
        }
    }
} 