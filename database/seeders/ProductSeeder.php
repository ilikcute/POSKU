<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::chunk(100, function ($products) {
            foreach ($products as $product) {
                $updates = [];

                if (! $product->member_price) {
                    $updates = [
                        'member_price' => round($product->selling_price * 0.95, 0),
                        'vip_price' => round($product->selling_price * 0.90, 0),
                        'wholesale_price' => round($product->selling_price * 0.85, 0),
                        'min_wholesale_qty' => rand(5, 20),
                    ];
                }

                if (! $product->tax_type) {
                    $updates['tax_type'] = 'Y';
                }

                if ($product->tax_rate === null) {
                    $updates['tax_rate'] = 11.0;
                }

                if ($product->final_price === null) {
                    $taxRate = $updates['tax_rate'] ?? $product->tax_rate ?? 0;
                    $taxType = $updates['tax_type'] ?? $product->tax_type ?? 'N';
                    $sellingPrice = $product->selling_price ?? 0;
                    $taxAmount = $taxType === 'Y' ? ($sellingPrice * $taxRate / 100) : 0;
                    $updates['final_price'] = round($sellingPrice + $taxAmount, 0);
                }

                if (! empty($updates)) {
                    $product->update($updates);
                }
            }
        });

        // Create new products if needed
        if (Product::count() < 50) {
            Product::factory(50)->create();
        }

        // Create some sample products with specific pricing
        $sampleProducts = [
            [
                'product_code' => 'PRD001',
                'name' => 'Sample Product 1',
                'description' => 'This is a sample product for testing',
                'purchase_price' => 70000,
                'selling_price' => 100000,
                'final_price' => 111000,
                'member_price' => 95000,
                'vip_price' => 90000,
                'wholesale_price' => 85000,
                'min_wholesale_qty' => 10,
                'unit' => 'Pcs',
                'min_stock_alert' => 5,
                'tax_type' => 'Y',
                'tax_rate' => 11.0,
            ],
            [
                'product_code' => 'PRD002',
                'name' => 'Premium Product',
                'description' => 'Premium quality product',
                'purchase_price' => 150000,
                'selling_price' => 200000,
                'final_price' => 222000,
                'member_price' => 190000,
                'vip_price' => 180000,
                'wholesale_price' => 170000,
                'min_wholesale_qty' => 5,
                'unit' => 'Box',
                'min_stock_alert' => 3,
                'tax_type' => 'Y',
                'tax_rate' => 11.0,
            ],
        ];

        foreach ($sampleProducts as $product) {
            Product::updateOrCreate(
                ['product_code' => $product['product_code']],
                $product
            );
        }
    }
}
