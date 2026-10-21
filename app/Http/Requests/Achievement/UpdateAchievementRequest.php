<?php

namespace App\Http\Requests\Achievement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAchievementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'image' => ['sometimes', 'string', 'max:255'],
            'criteria_type' => ['sometimes', 'in:games_played,games_won,total_shots,shot_highscore,single_game_shots,member_number'],
            'criteria_value' => ['sometimes', 'integer', 'min:0'],
            'exact_match' => ['sometimes', 'boolean'],
            'character_id' => ['sometimes', 'nullable', 'integer', 'exists:characters,id'],
            'available_from' => ['sometimes', 'nullable', 'date'],
            'available_until' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
