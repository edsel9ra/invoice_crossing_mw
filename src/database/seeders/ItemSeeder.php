<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => '393', 'name' => 'MISTER DRINK', 'tickets_per_unit' => 1, 'is_active' => true],
            ['code' => '394', 'name' => 'TEQUILA SUNRISE', 'tickets_per_unit' => 1, 'is_active' => true],
            ['code' => '621', 'name' => 'TEQUILA JOSE CUERVO BOTELLA', 'tickets_per_unit' => 3, 'is_active' => true],
            ['code' => '661', 'name' => 'MOJITO TEXANO', 'tickets_per_unit' => 1, 'is_active' => true],
            ['code' => '669', 'name' => 'CORONARITA MANGORITA JOSE CUERVO', 'tickets_per_unit' => 1, 'is_active' => true],
            ['code' => '670', 'name' => 'CORONARITA COCONUT JOSE CUERVO', 'tickets_per_unit' => 1, 'is_active' => true],
            ['code' => '671', 'name' => 'CORONARITA BLUE SKY JOSE CUERVO', 'tickets_per_unit' => 1, 'is_active' => true],
            ['code' => '672', 'name' => 'CORONARITA PASSION FRUIT JOSE CUERVO', 'tickets_per_unit' => 1, 'is_active' => true],
            ['code' => '673', 'name' => 'CORONARITA FLAMINGO JOSE CUERVO', 'tickets_per_unit' => 1, 'is_active' => true],
            ['code' => '674', 'name' => 'CORONARITA TRADITIONAL JOSE CUERVO', 'tickets_per_unit' => 1, 'is_active' => true],
            ['code' => '690', 'name' => 'MARGARITA MANGORITA JOSE CUERVO', 'tickets_per_unit' => 1, 'is_active' => true],
            ['code' => '691', 'name' => 'MARGARITA COCONUT JOSE CUERVO', 'tickets_per_unit' => 1, 'is_active' => true],
            ['code' => '692', 'name' => 'MARGARITA BLUE SKY JOSE CUERVO', 'tickets_per_unit' => 1, 'is_active' => true],
            ['code' => '693', 'name' => 'MARGARITA PASSION FRUIT JOSE CUERVO', 'tickets_per_unit' => 1, 'is_active' => true],
            ['code' => '694', 'name' => 'MARGARITA FLAMINGO JOSE CUERVO', 'tickets_per_unit' => 1, 'is_active' => true],
            ['code' => '695', 'name' => 'MARGARITA TRADITIONAL JOSE CUERVO', 'tickets_per_unit' => 1, 'is_active' => true],
            ['code' => '1706', 'name' => 'MOJITO PASSION FRUIT TEXANO', 'tickets_per_unit' => 1, 'is_active' => true],
            ['code' => '2035', 'name' => 'MARGARITA LOVE ON ROCKS JOSE CUERVO', 'tickets_per_unit' => 1, 'is_active' => true],
            ['code' => '2156', 'name' => 'MARGARITA MANGORITA JOSE CUERVO 2X3', 'tickets_per_unit' => 2, 'is_active' => true],
            ['code' => '2157', 'name' => 'MARGARITA COCONUT JOSE CUERVO 2X3', 'tickets_per_unit' => 2, 'is_active' => true],
            ['code' => '2158', 'name' => 'MARGARITA BLUE SKY JOSE CUERVO 2X3', 'tickets_per_unit' => 2, 'is_active' => true],
            ['code' => '2159', 'name' => 'MARGARITA PASSION FRUIT JOSE CUERVO 2X3', 'tickets_per_unit' => 2, 'is_active' => true],
            ['code' => '2160', 'name' => 'MARGARITA FLAMINGO JOSE CUERVO 2X3', 'tickets_per_unit' => 2, 'is_active' => true],
            ['code' => '2161', 'name' => 'MARGARITA TRADITIONAL JOSE CUERVO 2X3', 'tickets_per_unit' => 2, 'is_active' => true],
        ];

        foreach ($items as $item) {
            Item::query()->updateOrCreate(
                ['code' => $item['code']],
                $item
            );
        }
    }
}
