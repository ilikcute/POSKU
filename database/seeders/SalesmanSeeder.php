<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SalesmanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Salesman::factory(10)->create();
    }
}
