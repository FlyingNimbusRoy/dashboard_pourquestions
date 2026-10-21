<?php

namespace Database\Factories;

use App\Enums\AchievementCriteria;
use App\Models\Achievement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Achievement>
 */
class AchievementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'image' => 'achievements/'.fake()->slug(1).'.png',
            'criteria_type' => fake()->randomElement(AchievementCriteria::cases()),
            'criteria_value' => fake()->numberBetween(1, 100),
            'exact_match' => false,
            'character_id' => null,
        ];
    }
}
