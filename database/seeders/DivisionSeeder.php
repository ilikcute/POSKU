<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            ['code' => '1', 'name' => 'BREAKFAST FOOD'],
            ['code' => '2', 'name' => 'MILK'],
            ['code' => '3', 'name' => 'BABY FOOD'],
            ['code' => '4', 'name' => 'BEVERAGES'],
            ['code' => '5', 'name' => 'BASIC FOOD'],
        ];

        foreach ($divisions as $division) {
            Division::updateOrCreate(
                ['code' => $division['code']],
                ['name' => $division['name']]
            );
        }
    }
}
