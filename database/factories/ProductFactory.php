<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Division;
use App\Models\Rack;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $productName = $this->faker->words(3, true);
        $purchase_price = $this->faker->numberBetween(10000, 100000);
        $selling_price = $purchase_price * 1.3; // Ambil margin 30%

        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'product_code' => 'P'.$this->faker->unique()->numerify('#####'),
            'barcode' => $this->faker->unique()->ean13(),
            'purchase_price' => $selling_price * 0.7,
            'selling_price' => $selling_price,
            'member_price' => $selling_price * 0.95, // 5% discount
            'vip_price' => $selling_price * 0.90, // 10% discount
            'wholesale_price' => $selling_price * 0.85, // 15% discount
            'min_wholesale_qty' => fake()->numberBetween(5, 20),
            'category_id' => Category::inRandomOrder()->first()->id ?? \App\Models\Category::factory(),
            'supplier_id' => Supplier::inRandomOrder()->first()->id ?? \App\Models\Supplier::factory(),
            'rack_id' => Rack::inRandomOrder()->first()->id ?? \App\Models\Rack::factory(),
            'division_id' => Division::inRandomOrder()->first()->id ?? \App\Models\Division::factory(),
            'unit' => fake()->randomElement(['Pcs', 'Box', 'Kg', 'Liter', 'Pack']),
            'min_stock_alert' => fake()->numberBetween(5, 50),
        ];
    }
}
