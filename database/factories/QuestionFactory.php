<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'maker_id' => 1,
            'vraag' => rtrim(fake()->sentence(8), '.').'?',
            'difficulty' => 3,
            'last_validated' => Question::NEVER_VALIDATED,
        ];
    }

    public function validated(?\DateTimeInterface $at = null): static
    {
        return $this->state(['last_validated' => $at ?? now()]);
    }

    /** Adds answers; the first one is the correct answer. */
    public function withAnswers(string $correct, string ...$wrong): static
    {
        return $this->afterCreating(function (Question $question) use ($correct, $wrong) {
            $question->answers()->create(['answer' => $correct, 'is_correct' => true]);
            foreach ($wrong as $answer) {
                $question->answers()->create(['answer' => $answer, 'is_correct' => false]);
            }
        });
    }
}
