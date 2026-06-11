<?php

namespace Database\Seeders;

use App\Models\InvoiceSeriesBranch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['series_number' => '111P', 'branch_name' => 'Pance', 'is_active' => true],
            ['series_number' => '123P', 'branch_name' => 'Jardín Plaza', 'is_active' => true],
            ['series_number' => '131P', 'branch_name' => 'Ciudad Jardín', 'is_active' => true],
            ['series_number' => '141P', 'branch_name' => 'Unicentro', 'is_active' => true],
            ['series_number' => '152P', 'branch_name' => 'Granada', 'is_active' => true],
            ['series_number' => '161P', 'branch_name' => 'Limonar', 'is_active' => true],
            ['series_number' => '171P', 'branch_name' => 'San Fernando', 'is_active' => true],
            ['series_number' => '181P', 'branch_name' => 'Flora', 'is_active' => true],
            ['series_number' => '191P', 'branch_name' => 'Chipichape', 'is_active' => true],
            ['series_number' => '210P', 'branch_name' => 'Llanogrande', 'is_active' => true],
            ['series_number' => '220P', 'branch_name' => 'Bochalema', 'is_active' => true],
        ];

        foreach ($branches as $branch) {
            InvoiceSeriesBranch::query()->updateOrCreate(
                ['series_number' => $branch['series_number']],
                $branch
            );
        }
    }
}
