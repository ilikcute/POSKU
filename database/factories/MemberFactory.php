<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'store_id' => 1,
            'member_code' => 'M' . $this->faker->unique()->numerify('######'),
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'address' => $this->faker->address,
            'points' => $this->faker->numberBetween(0, 1000),
            'photo' => null,
            'status' => 'active',
            'joined_at' => $this->faker->dateTimeBetween('-1 years', 'now'),
        ];
    }
}
