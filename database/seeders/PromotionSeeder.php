<?php

namespace Database\Seeders;

use App\Models\CustomerType;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample promotions
        $promotions = [
            [
                'name' => 'Flash Sale Weekend',
                'description' => 'Diskon 20% untuk produk tertentu di akhir pekan',
                'promotion_type' => 'product_discount',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'min_purchase_amount' => 100000,
                'max_discount_amount' => 50000,
                'min_quantity' => 1,
                'start_date' => now()->subDays(7),
                'end_date' => now()->addDays(30),
                'is_stackable' => false,
                'is_active' => true,
                'usage_limit' => 100,
                'usage_count' => 15,
            ],
            [
                'name' => 'Member Special 15%',
                'description' => 'Diskon khusus 15% untuk member',
                'promotion_type' => 'product_discount',
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'min_purchase_amount' => 50000,
                'min_quantity' => 1,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(60),
                'is_stackable' => true,
                'is_active' => true,
                'usage_limit' => 500,
            ],
            [
                'name' => 'Buy 3 Get 1 Free',
                'description' => 'Beli 3 gratis 1 untuk produk tertentu',
                'promotion_type' => 'buy_get',
                'discount_type' => 'percentage',
                'discount_value' => 25.00,
                'min_quantity' => 3,
                'start_date' => now()->subDays(3),
                'end_date' => now()->addDays(45),
                'is_stackable' => false,
                'is_active' => true,
            ],
            [
                'name' => 'New Year Cashback',
                'description' => 'Cashback Rp 25,000 untuk pembelian minimal Rp 250,000',
                'promotion_type' => 'cashback',
                'discount_type' => 'fixed_amount',
                'discount_value' => 25000,
                'min_purchase_amount' => 250000,
                'min_quantity' => 1,
                'start_date' => now()->addDays(1),
                'end_date' => now()->addDays(90),
                'is_stackable' => true,
                'is_active' => true,
                'usage_limit' => 200,
            ],
            [
                'name' => 'VIP Exclusive 30%',
                'description' => 'Diskon eksklusif 30% untuk VIP customer',
                'promotion_type' => 'product_discount',
                'discount_type' => 'percentage',
                'discount_value' => 30.00,
                'max_discount_amount' => 100000,
                'min_quantity' => 1,
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(14),
                'is_stackable' => false,
                'is_active' => true,
                'usage_limit' => 50,
            ],
        ];

        $createdPromotions = [];
        foreach ($promotions as $promoData) {
            $createdPromotions[] = Promotion::updateOrCreate(
                ['name' => $promoData['name']],
                $promoData
            );
        }

        // Create additional random promotions
        $randomPromotions = Promotion::factory(10)->create();
        $createdPromotions = array_merge($createdPromotions, $randomPromotions->toArray());

        // Assign products to promotions
        $products = Product::take(20)->get();
        foreach ($createdPromotions as $promotion) {
            if ($promotion instanceof Promotion) {
                // Attach random products to each promotion
                $randomProducts = $products->random(rand(2, 8));
                $promotion->products()->sync($randomProducts->pluck('id')->toArray());
            }
        }

        // Assign customer types to specific promotions
        $memberType = CustomerType::where('code', 'MEMBER')->first();
        $vipType = CustomerType::where('code', 'VIP')->first();
        $regularType = CustomerType::where('code', 'REGULAR')->first();

        // Member Special promotion only for members
        $memberPromo = Promotion::where('name', 'Member Special 15%')->first();
        if ($memberPromo && $memberType) {
            $memberPromo->customerTypes()->sync([$memberType->id]);
        }

        // VIP Exclusive promotion only for VIP
        $vipPromo = Promotion::where('name', 'VIP Exclusive 30%')->first();
        if ($vipPromo && $vipType) {
            $vipPromo->customerTypes()->sync([$vipType->id]);
        }

        // Flash Sale for all customer types
        $flashSalePromo = Promotion::where('name', 'Flash Sale Weekend')->first();
        if ($flashSalePromo) {
            $allTypes = CustomerType::pluck('id')->toArray();
            $flashSalePromo->customerTypes()->sync($allTypes);
        }
    }
}
