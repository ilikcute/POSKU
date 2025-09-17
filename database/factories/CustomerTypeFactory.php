<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerType>
 */
class CustomerTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'code' => fake()->unique()->lexify('????'),
            'discount_percentage' => fake()->randomFloat(2, 0, 25),
            'description' => fake()->optional()->sentence(),
            'is_active' => fake()->boolean(90),
        ];
    }

    public function active()
    {
        return $this->state(['is_active' => true]);
    }

    public function inactive()
    {
        return $this->state(['is_active' => false]);
    }
}
