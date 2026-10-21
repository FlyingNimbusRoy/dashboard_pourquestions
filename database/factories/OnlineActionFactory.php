<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\OnlineAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OnlineAction>
 */
class OnlineActionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'host' => false,
            'user_csrf' => fake()->sha1(),
            'action' => null,
        ];
    }
}
