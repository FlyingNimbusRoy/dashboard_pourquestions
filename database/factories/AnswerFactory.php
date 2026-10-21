<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Answer>
 */
class AnswerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'answer' => fake()->word(),
            'is_correct' => false,
        ];
    }
}
