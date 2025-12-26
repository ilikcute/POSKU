<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RackFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Rak '.$this->faker->bothify('?-##'),
            'rack_code' => strtoupper($this->faker->bothify('?-##')),
            'rack_type' => $this->faker->randomElement(['Single', 'Gondola']),
            'shelf_count' => $this->faker->numberBetween(3, 6),
            'shelf_plan' => [],
        ];
    }
}
