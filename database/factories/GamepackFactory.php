<?php

namespace Database\Factories;

use App\Models\Gamepack;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gamepack>
 */
class GamepackFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'category' => 'questionpack',
        ];
    }
}
