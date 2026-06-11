<?php

namespace Database\Seeders;

use App\Models\InvoiceSeriesBranch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['series_number' => '220P', 'branch_name' => 'Sucursal Centro', 'is_active' => true],
            ['series_number' => '221P', 'branch_name' => 'Sucursal Norte', 'is_active' => true],
            ['series_number' => '222P', 'branch_name' => 'Sucursal Sur', 'is_active' => true],
            ['series_number' => '223P', 'branch_name' => 'Sucursal Este', 'is_active' => true],
            ['series_number' => '224P', 'branch_name' => 'Sucursal Oeste', 'is_active' => false],
        ];

        foreach ($branches as $branch) {
            InvoiceSeriesBranch::query()->updateOrCreate(
                ['series_number' => $branch['series_number']],
                $branch
            );
        }
    }
}
