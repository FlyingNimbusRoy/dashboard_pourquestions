<?php

namespace App\Http\Requests\UserGamestats;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserGamestatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'games_played' => ['sometimes', 'integer', 'min:0'],
            'games_won' => ['sometimes', 'integer', 'min:0'],
            'games_lost' => ['sometimes', 'integer', 'min:0'],
            'total_shots' => ['sometimes', 'integer', 'min:0'],
            'most_shots' => ['sometimes', 'integer', 'min:0'],
            'least_shots' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'shot_highscore' => ['sometimes', 'integer', 'min:0'],
            'question_highscore' => ['sometimes', 'integer', 'min:0'],
            'modifiers' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
