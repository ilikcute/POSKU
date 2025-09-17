<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DivisionFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => ucfirst($this->faker->word)];
    }
}
