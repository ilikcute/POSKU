<?php

namespace Database\Seeders;

use App\Models\Rack;
use Illuminate\Database\Seeder;

class RackSeeder extends Seeder
{
    public function run(): void
    {
        Rack::create(['name' => 'A-01']);
        Rack::create(['name' => 'A-02']);
        Rack::create(['name' => 'B-01']);
        Rack::factory(10)->create();
    }
}
