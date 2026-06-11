<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => 'ART001', 'name' => 'Item A - Ejemplo', 'tickets_per_unit' => 1, 'is_active' => true],
            ['code' => 'ART002', 'name' => 'Item B - Ejemplo', 'tickets_per_unit' => 2, 'is_active' => true],
            ['code' => 'ART003', 'name' => 'Item C - Ejemplo', 'tickets_per_unit' => 3, 'is_active' => true],
            ['code' => 'ART004', 'name' => 'Item D - Inactivo', 'tickets_per_unit' => 1, 'is_active' => false],
            ['code' => 'ART005', 'name' => 'Item E - Sin boletas', 'tickets_per_unit' => 0, 'is_active' => true],
        ];

        foreach ($items as $item) {
            Item::query()->updateOrCreate(
                ['code' => $item['code']],
                $item
            );
        }
    }
}
