<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PromotionFactory extends Factory
{
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+3 months');

        return [
            'name' => fake()->words(3, true).' Promotion',
            'description' => fake()->optional(0.8)->paragraph(),
            'promotion_type' => fake()->randomElement(['product_discount', 'buy_get', 'cashback']),
            'discount_type' => fake()->randomElement(['percentage', 'fixed_amount']),
            'discount_value' => fake()->randomFloat(2, 5, 50),
            'min_purchase_amount' => fake()->optional(0.6)->randomFloat(2, 50000, 500000),
            'max_discount_amount' => fake()->optional(0.4)->randomFloat(2, 10000, 100000),
            'min_quantity' => fake()->numberBetween(1, 5),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_stackable' => fake()->boolean(30),
            'is_active' => fake()->boolean(80),
            'usage_limit' => fake()->optional(0.5)->numberBetween(10, 1000),
            'usage_count' => 0,
        ];
    }

    public function active()
    {
        return $this->state([
            'is_active' => true,
            'start_date' => fake()->dateTimeBetween('-1 week', 'now'),
            'end_date' => fake()->dateTimeBetween('+1 week', '+2 months'),
        ]);
    }

    public function percentage()
    {
        return $this->state([
            'discount_type' => 'percentage',
            'discount_value' => fake()->randomFloat(2, 5, 50),
        ]);
    }

    public function fixedAmount()
    {
        return $this->state([
            'discount_type' => 'fixed_amount',
            'discount_value' => fake()->randomFloat(2, 5000, 100000),
        ]);
    }
}
