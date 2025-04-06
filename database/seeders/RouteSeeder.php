<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Route;
use App\Models\User;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        $routes = [
            [
                'name' => 'Средиземноморский гастротур',
                'description' => 'Путешествие по средиземноморской кухне: от Италии до Греции',
                'countries' => [
                    ['id' => 1, 'order' => 0], // Италия
                    ['id' => 2, 'order' => 1], // Греция
                ],
            ],
            [
                'name' => 'Азиатский кулинарный тур',
                'description' => 'Знакомство с кухнями Азии: от Японии до Таиланда',
                'countries' => [
                    ['id' => 3, 'order' => 0], // Япония
                    ['id' => 4, 'order' => 1], // Таиланд
                ],
            ],
            [
                'name' => 'Европейский гастротур',
                'description' => 'Путешествие по лучшим ресторанам Европы',
                'countries' => [
                    ['id' => 5, 'order' => 0], // Франция
                    ['id' => 6, 'order' => 1], // Испания
                ],
            ],
        ];

        foreach ($routes as $routeData) {
            $route = Route::create([
                'name' => $routeData['name'],
                'description' => $routeData['description'],
                'user_id' => $user->id,
            ]);

            foreach ($routeData['countries'] as $country) {
                $route->countries()->attach($country['id'], ['order' => $country['order']]);
            }
        }
    }
} 