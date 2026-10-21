<?php

namespace Database\Factories;

use App\Models\Modifier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Modifier>
 */
class ModifierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'turnbased' => false,
            'effects' => [],
            'coupled_gamepack_id' => null,
            'is_active' => true,
        ];
    }
}
