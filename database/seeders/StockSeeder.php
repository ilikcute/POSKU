<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        foreach ($products as $product) {
            Stock::create([
                'product_id' => $product->id,
                'quantity' => rand(10, 100), // Isi stok awal dengan angka random
            ]);
        }
    }
}
