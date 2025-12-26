<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Division;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $divisionMap = Division::query()->pluck('id', 'code');

        $categories = [
            ['division_code' => '1', 'code' => '1', 'name' => 'TEH CELUP'],
            ['division_code' => '1', 'code' => '2', 'name' => 'TEH BUBUK'],
            ['division_code' => '2', 'code' => '1', 'name' => 'SUSU KENTAL MANIS'],
            ['division_code' => '2', 'code' => '2', 'name' => 'INSTANT MILK POWDER'],
            ['division_code' => '3', 'code' => '1', 'name' => 'BABY MILK'],
            ['division_code' => '3', 'code' => '2', 'name' => 'BABY CEREAL'],
            ['division_code' => '4', 'code' => '1', 'name' => 'LIQUID MILK TETRA'],
            ['division_code' => '4', 'code' => '2', 'name' => 'LIQUID MILK BTL'],
            ['division_code' => '5', 'code' => '1', 'name' => 'BERAS'],
            ['division_code' => '5', 'code' => '2', 'name' => 'GULA (GULA OLAHAN)'],
            ['division_code' => '5', 'code' => '3', 'name' => 'BIJI-BIJIAN LOKAL'],
        ];

        foreach ($categories as $category) {
            $divisionId = $divisionMap[$category['division_code']] ?? null;
            if (! $divisionId) {
                continue;
            }

            Category::updateOrCreate(
                [
                    'division_id' => $divisionId,
                    'code' => $category['code'],
                ],
                ['name' => $category['name']]
            );
        }
    }
}
