<?php

namespace Database\Factories;

use App\Models\Character;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Character>
 */
class CharacterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->firstName(),
            'url' => fake()->unique()->slug(2),
            'gamepack_id' => null,
            'parent_character_id' => null,
            'show_on_homepage' => true,
        ];
    }
}
