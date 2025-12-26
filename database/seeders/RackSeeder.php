<?php

namespace Database\Seeders;

use App\Models\Rack;
use App\Models\RackShelf;
use Illuminate\Database\Seeder;

class RackSeeder extends Seeder
{
    public function run(): void
    {
        $racks = [
            ['name' => 'Rak A-01', 'rack_code' => 'A-01', 'rack_type' => 'Single', 'shelf_count' => 4],
            ['name' => 'Rak A-02', 'rack_code' => 'A-02', 'rack_type' => 'Single', 'shelf_count' => 4],
            ['name' => 'Rak B-01', 'rack_code' => 'B-01', 'rack_type' => 'Gondola', 'shelf_count' => 5],
        ];

        foreach ($racks as $rackData) {
            $rack = Rack::create(array_merge($rackData, ['shelf_plan' => []]));
            for ($i = 1; $i <= $rack->shelf_count; $i++) {
                RackShelf::create([
                    'rack_id' => $rack->id,
                    'shelf_no' => $i,
                ]);
            }
        }

        Rack::factory(10)->create()->each(function (Rack $rack) {
            for ($i = 1; $i <= (int) ($rack->shelf_count ?? 0); $i++) {
                RackShelf::create([
                    'rack_id' => $rack->id,
                    'shelf_no' => $i,
                ]);
            }
        });
    }
}
