<?php

namespace Database\Factories;

use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'division_id' => Division::query()->inRandomOrder()->value('id') ?? Division::factory(),
            'name' => $this->faker->words(2, true),
        ];
    }
}
