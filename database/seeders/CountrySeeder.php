<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'id' => 1,
                'name' => 'Италия',
                'code' => 'IT',
                'flag' => '🇮🇹',
                'description' => 'Страна с богатой кулинарной историей и традициями',
            ],
            [
                'id' => 2,
                'name' => 'Греция',
                'code' => 'GR',
                'flag' => '🇬🇷',
                'description' => 'Колыбель средиземноморской кухни',
            ],
            [
                'id' => 3,
                'name' => 'Япония',
                'code' => 'JP',
                'flag' => '🇯🇵',
                'description' => 'Страна с уникальной и изысканной кухней',
            ],
            [
                'id' => 4,
                'name' => 'Таиланд',
                'code' => 'TH',
                'flag' => '🇹🇭',
                'description' => 'Страна с яркой и острой кухней',
            ],
            [
                'id' => 5,
                'name' => 'Франция',
                'code' => 'FR',
                'flag' => '🇫🇷',
                'description' => 'Страна высокой кухни и изысканных вин',
            ],
            [
                'id' => 6,
                'name' => 'Испания',
                'code' => 'ES',
                'flag' => '🇪🇸',
                'description' => 'Страна с разнообразной и вкусной кухней',
            ],
            [
                'name' => 'Мексика',
                'code' => 'MX',
                'flag' => '🇲🇽',
                'description' => 'Страна с яркой и острой кухней, известная своими тако, гуакамоле и текилой.'
            ],
            [
                'name' => 'Индия',
                'code' => 'IN',
                'flag' => '🇮🇳',
                'description' => 'Страна с разнообразной кухней, известная своими карри, самосой и чаем.'
            ],
            [
                'name' => 'Китай',
                'code' => 'CN',
                'flag' => '🇨🇳',
                'description' => 'Страна с разнообразной кухней, известная своими пельменями, уткой по-пекински и чаем.'
            ],
            [
                'name' => 'Турция',
                'code' => 'TR',
                'flag' => '🇹🇷',
                'description' => 'Страна с богатой кулинарной традицией, известная своими кебабами, баклавой и кофе.'
            ]
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
} 