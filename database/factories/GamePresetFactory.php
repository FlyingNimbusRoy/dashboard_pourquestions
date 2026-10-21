<?php

namespace Database\Factories;

use App\Models\GamePreset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GamePreset>
 */
class GamePresetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'rounds' => 10,
            'is_nsfw' => false,
            'pack_ids' => [],
        ];
    }
}
