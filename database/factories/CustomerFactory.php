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
            'name' => fake()->name(),
            'email' => fake()->optional(0.8)->safeEmail(),
            'phone' => fake()->optional(0.9)->phoneNumber(),
            'address' => fake()->optional(0.7)->address(),
            'city' => fake()->optional(0.7)->city(),
            'state' => fake()->optional(0.7)->state(),
            'country' => fake()->optional(0.7)->country(),
            'zip_code' => fake()->optional(0.7)->postcode(),
            'customer_type_id' => CustomerType::factory(),
            'status' => 'active',
            'joined_at' => fake()->dateTimeBetween('-1 years', 'now'),
            'points' => fake()->numberBetween(0, 1000),
        ];
    }

    public function withType($customerTypeId)
    {
        return $this->state(['customer_type_id' => $customerTypeId]);
    }
}
