<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        Division::create(['name' => 'Makanan']);
        Division::create(['name' => 'Minuman']);
        Division::create(['name' => 'Peralatan Mandi']);
        Division::factory(5)->create();
    }
}
