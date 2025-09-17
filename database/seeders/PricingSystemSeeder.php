<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PricingSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            CustomerTypeSeeder::class,
            CustomerSeeder::class,
            ProductSeeder::class,
            PromotionSeeder::class,
        ]);
    }
}
