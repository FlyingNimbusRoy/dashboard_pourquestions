<?php

namespace Database\Factories;

use App\Models\SetupPreset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SetupPreset>
 */
class SetupPresetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->lexify('????????'),
            'game_length' => 10,
            'is_nsfw' => true,
            'burst_mode' => false,
            'excluded_packs' => [],
            'excluded_categories' => [],
        ];
    }
}
