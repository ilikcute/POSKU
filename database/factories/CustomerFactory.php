<?php

namespace Database\Factories;

use App\Models\CustomerType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_code' => fake()->unique()->lexify('CUST????'),
            'member_code' => fake()->optional(0.5)->unique()->lexify('MEMB????'),
            'name' => fake()->name(),
            'email' => fake()->optional(0.8)->safeEmail(),
            'phone' => fake()->optional(0.9)->phoneNumber(),
            'address' => fake()->optional(0.7)->address(),
            'customer_type_id' => CustomerType::factory(),
            'is_active' => fake()->boolean(95),
            'store_id' => 1,
            'points' => fake()->numberBetween(0, 1000),
            'photo' => null,
            'status' => 'active',
            'joined_at' => fake()->dateTimeBetween('-1 years', 'now'),
        ];
    }

    public function active()
    {
        return $this->state(['is_active' => true]);
    }

    public function withType($customerTypeId)
    {
        return $this->state(['customer_type_id' => $customerTypeId]);
    }
}
