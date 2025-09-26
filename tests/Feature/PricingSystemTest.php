<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingSystemTest extends TestCase
{
    use RefreshDatabase;

    protected CustomerType $regularType;

    protected CustomerType $customerType;

    protected CustomerType $vipType;

    protected CustomerType $wholesaleType;

    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\StoreSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        // Create customer types
        $this->regularType = CustomerType::factory()->create(['code' => 'REGULAR']);
        $this->customerType = CustomerType::factory()->create(['code' => 'MEMBER']);
        $this->vipType = CustomerType::factory()->create(['code' => 'VIP']);
        $this->wholesaleType = CustomerType::factory()->create(['code' => 'WHOLESALE']);
    }

    public function test_regular_customer_gets_selling_price()
    {
        $product = Product::factory()->create([
            'selling_price' => 100000,
            'member_price' => 95000,
            'vip_price' => 90000,
        ]);

        $customer = Customer::factory()->create(['customer_type_id' => $this->regularType->id]);

        $price = $product->getPrice($customer->customerType->code);

        $this->assertEquals(100000, $price);
    }

    public function test_customer_gets_member_price()
    {
        $product = Product::factory()->create([
            'selling_price' => 100000,
            'member_price' => 95000,
        ]);

        $customer = Customer::factory()->create(['customer_type_id' => $this->customerType->id]);

        $price = $product->getPrice($customer->customerType->code);

        $this->assertEquals(95000, $price);
    }

    public function test_vip_gets_vip_price()
    {
        $product = Product::factory()->create([
            'selling_price' => 100000,
            'vip_price' => 90000,
        ]);

        $customer = Customer::factory()->create(['customer_type_id' => $this->vipType->id]);

        $price = $product->getPrice($customer->customerType->code);

        $this->assertEquals(90000, $price);
    }

    public function test_wholesale_price_with_minimum_quantity()
    {
        $product = Product::factory()->create([
            'selling_price' => 100000,
            'wholesale_price' => 85000,
            'min_wholesale_qty' => 10,
        ]);

        $customer = Customer::factory()->create(['customer_type_id' => $this->wholesaleType->id]);

        // Test with quantity below minimum
        $price = $product->getPrice($customer->customerType->code, 5);
        $this->assertEquals(100000, $price);

        // Test with quantity above minimum
        $price = $product->getPrice($customer->customerType->code, 10);
        $this->assertEquals(85000, $price);
    }

    public function test_promotion_applies_correctly()
    {
        $product = Product::factory()->create([
            'selling_price' => 100000,
        ]);

        $promotion = Promotion::factory()->active()->create([
            'discount_type' => 'percentage',
            'discount_value' => 20,
        ]);

        $promotion->products()->attach($product->id);

        $customer = Customer::factory()->create(['customer_type_id' => $this->regularType->id]);

        $pricing = $product->getPriceWithPromotion($customer);

        $this->assertEquals(100000, $pricing['original_price']);
        $this->assertEquals(80000, $pricing['final_price']);
        $this->assertEquals(20000, $pricing['discount_amount']);
    }

    public function test_api_price_check()
    {
        $product = Product::factory()->create(['selling_price' => 100000]);
        $customer = Customer::factory()->create(['customer_type_id' => $this->customerType->id]);

        $response = $this->postJson('/api/price/check', [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'product',
                'customer',
                'customer_type',
                'quantity',
                'pricing' => [
                    'original_price',
                    'final_price',
                    'discount_amount',
                    'promotion_name',
                ],
            ]);
    }
}
