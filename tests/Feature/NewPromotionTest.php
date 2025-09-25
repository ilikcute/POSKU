<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\PromotionTier;
use App\Models\PromotionBundle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewPromotionTest extends TestCase
{
    use RefreshDatabase;

    protected CustomerType $regularType;
    protected CustomerType $vipType;

    protected function setUp(): void
    {
        parent::setUp();

        // Create customer types
        $this->regularType = CustomerType::factory()->create(['code' => 'REGULAR']);
        $this->vipType = CustomerType::factory()->create(['code' => 'VIP']);
    }

    public function test_tiered_pricing_promotion()
    {
        $product = Product::factory()->create(['selling_price' => 100000]);

        $promotion = Promotion::factory()->create([
            'promotion_type' => 'tiered_pricing',
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $promotion->products()->attach($product->id);

        // Create tiers
        PromotionTier::create([
            'promotion_id' => $promotion->id,
            'min_quantity' => 1,
            'discount_type' => 'percentage',
            'discount_value' => 10,
        ]);

        PromotionTier::create([
            'promotion_id' => $promotion->id,
            'min_quantity' => 5,
            'discount_type' => 'percentage',
            'discount_value' => 20,
        ]);

        PromotionTier::create([
            'promotion_id' => $promotion->id,
            'min_quantity' => 10,
            'discount_type' => 'fixed_amount',
            'discount_value' => 15000,
        ]);

        $customer = Customer::factory()->create(['customer_type_id' => $this->regularType->id]);

        // Test quantity 1: 10% discount
        $pricing = $product->getPriceWithPromotion($customer, 1);
        $this->assertEquals(100000, $pricing['original_price']);
        $this->assertEquals(90000, $pricing['final_price']);
        $this->assertEquals(10000, $pricing['discount_amount']);

        // Test quantity 5: 20% discount
        $pricing = $product->getPriceWithPromotion($customer, 5);
        $this->assertEquals(100000, $pricing['original_price']);
        $this->assertEquals(80000, $pricing['final_price']);
        $this->assertEquals(20000, $pricing['discount_amount']);

        // Test quantity 10: fixed discount 15000
        $pricing = $product->getPriceWithPromotion($customer, 10);
        $this->assertEquals(100000, $pricing['original_price']);
        $this->assertEquals(85000, $pricing['final_price']);
        $this->assertEquals(15000, $pricing['discount_amount']);
    }

    public function test_customer_type_based_promotion()
    {
        $product = Product::factory()->create(['selling_price' => 100000]);

        $promotion = Promotion::factory()->create([
            'promotion_type' => 'product_discount',
            'discount_type' => 'percentage',
            'discount_value' => 15,
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $promotion->products()->attach($product->id);
        $promotion->customerTypes()->attach($this->vipType->id);

        $regularCustomer = Customer::factory()->create(['customer_type_id' => $this->regularType->id]);
        $vipCustomer = Customer::factory()->create(['customer_type_id' => $this->vipType->id]);

        // Regular customer should not get discount
        $pricing = $product->getPriceWithPromotion($regularCustomer, 1);
        $this->assertEquals(100000, $pricing['final_price']);
        $this->assertEquals(0, $pricing['discount_amount']);

        // VIP customer should get 15% discount
        $pricing = $product->getPriceWithPromotion($vipCustomer, 1);
        $this->assertEquals(85000, $pricing['final_price']);
        $this->assertEquals(15000, $pricing['discount_amount']);
    }

    public function test_bundling_promotion()
    {
        $productA = Product::factory()->create(['selling_price' => 50000]);
        $productB = Product::factory()->create(['selling_price' => 30000]);

        $promotion = Promotion::factory()->create([
            'promotion_type' => 'bundling',
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $promotion->products()->attach($productA->id);

        // Create bundle: Buy 2 A, get 1 B free
        PromotionBundle::create([
            'promotion_id' => $promotion->id,
            'buy_product_id' => $productA->id,
            'buy_quantity' => 2,
            'get_product_id' => $productB->id,
            'get_quantity' => 1,
            'get_price' => 0,
        ]);

        $customer = Customer::factory()->create(['customer_type_id' => $this->regularType->id]);

        // Test with quantity 2: should get 1 free B
        $pricing = $productA->getPriceWithPromotion($customer, 2);
        $this->assertEquals(50000, $pricing['original_price']);
        $this->assertEquals(50000, $pricing['final_price']); // No discount on A
        $this->assertCount(1, $pricing['bundled_products']);
        $this->assertEquals($productB->id, $pricing['bundled_products'][0]['product_id']);
        $this->assertEquals(1, $pricing['bundled_products'][0]['quantity']);
        $this->assertEquals(0, $pricing['bundled_products'][0]['price']);

        // Test with quantity 4: should get 2 free B
        $pricing = $productA->getPriceWithPromotion($customer, 4);
        $this->assertCount(1, $pricing['bundled_products']);
        $this->assertEquals(2, $pricing['bundled_products'][0]['quantity']);
    }

    public function test_bundling_with_discounted_get_product()
    {
        $productA = Product::factory()->create(['selling_price' => 50000]);
        $productB = Product::factory()->create(['selling_price' => 30000]);

        $promotion = Promotion::factory()->create([
            'promotion_type' => 'bundling',
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $promotion->products()->attach($productA->id);

        // Create bundle: Buy 1 A, get 1 B at 50% discount
        PromotionBundle::create([
            'promotion_id' => $promotion->id,
            'buy_product_id' => $productA->id,
            'buy_quantity' => 1,
            'get_product_id' => $productB->id,
            'get_quantity' => 1,
            'get_price' => 15000, // Half price
        ]);

        $customer = Customer::factory()->create(['customer_type_id' => $this->regularType->id]);

        $pricing = $productA->getPriceWithPromotion($customer, 1);
        $this->assertCount(1, $pricing['bundled_products']);
        $this->assertEquals($productB->id, $pricing['bundled_products'][0]['product_id']);
        $this->assertEquals(1, $pricing['bundled_products'][0]['quantity']);
        $this->assertEquals(15000, $pricing['bundled_products'][0]['price']);
    }

    public function test_promotion_combination_priority()
    {
        $product = Product::factory()->create(['selling_price' => 100000]);

        // Create tiered pricing promotion
        $tieredPromotion = Promotion::factory()->create([
            'promotion_type' => 'tiered_pricing',
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $tieredPromotion->products()->attach($product->id);

        PromotionTier::create([
            'promotion_id' => $tieredPromotion->id,
            'min_quantity' => 5,
            'discount_type' => 'percentage',
            'discount_value' => 20,
        ]);

        // Create customer type promotion
        $customerPromotion = Promotion::factory()->create([
            'promotion_type' => 'product_discount',
            'discount_type' => 'percentage',
            'discount_value' => 15,
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $customerPromotion->products()->attach($product->id);
        $customerPromotion->customerTypes()->attach($this->vipType->id);

        $vipCustomer = Customer::factory()->create(['customer_type_id' => $this->vipType->id]);

        // For VIP customer with quantity 5, should get the higher discount (20% from tiered)
        $pricing = $product->getPriceWithPromotion($vipCustomer, 5);
        $this->assertEquals(80000, $pricing['final_price']); // 20% discount
        $this->assertEquals(20000, $pricing['discount_amount']);
    }

    public function test_promotion_expiry()
    {
        $product = Product::factory()->create(['selling_price' => 100000]);

        $promotion = Promotion::factory()->create([
            'promotion_type' => 'product_discount',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'is_active' => true,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
        ]);

        $promotion->products()->attach($product->id);

        $customer = Customer::factory()->create(['customer_type_id' => $this->regularType->id]);

        // Promotion not yet started
        $pricing = $product->getPriceWithPromotion($customer, 1);
        $this->assertEquals(100000, $pricing['final_price']);
        $this->assertNull($pricing['promotion_name']);
    }
}
