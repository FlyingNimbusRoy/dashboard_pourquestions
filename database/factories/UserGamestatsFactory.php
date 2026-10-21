<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserGamestats;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserGamestats>
 */
class UserGamestatsFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'games_played' => 0,
            'games_won' => 0,
            'games_lost' => 0,
            'total_shots' => 0,
            'most_shots' => 0,
            'least_shots' => null,
            'shot_highscore' => 0,
            'question_highscore' => 0,
            'modifiers' => [],
        ];
    }
}
