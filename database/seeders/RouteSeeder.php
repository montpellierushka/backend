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
        $countries = Country::all();

        $routes = [
            [
                'title' => 'Средиземноморский гастротур',
                'description' => 'Путешествие по средиземноморской кухне: от Италии до Греции',
                'duration' => 14,
                'countries' => ['IT', 'GR', 'ES']
            ],
            [
                'title' => 'Азиатский кулинарный тур',
                'description' => 'Знакомство с разнообразной азиатской кухней',
                'duration' => 21,
                'countries' => ['JP', 'CN', 'TH']
            ]
        ];

        foreach ($routes as $routeData) {
            $route = Route::create([
                'title' => $routeData['title'],
                'description' => $routeData['description'],
                'duration' => $routeData['duration'],
                'user_id' => $user->id
            ]);

            foreach ($routeData['countries'] as $countryCode) {
                $country = $countries->where('code', $countryCode)->first();
                if ($country) {
                    $route->countries()->attach($country->id, ['order' => array_search($countryCode, $routeData['countries'])]);
                }
            }
        }
    }
} 