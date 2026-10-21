<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'shotstaken' => 0,
            'players' => fake()->numberBetween(2, 8),
            'game_length' => fake()->numberBetween(5, 30),
            'is_nsfw' => false,
            'is_finished' => false,
            'is_cancelled' => false,
            'host_id' => User::factory(),
            'question_array' => [],
        ];
    }
}
