<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Toko Cabang '.$this->faker->city,
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'is_main_store' => false,
        ];
    }
}
