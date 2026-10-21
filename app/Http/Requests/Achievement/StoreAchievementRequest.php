<?php

namespace App\Http\Requests\Achievement;

use Illuminate\Foundation\Http\FormRequest;

class StoreAchievementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'image' => ['required', 'string', 'max:255'],
            'criteria_type' => ['required', 'in:games_played,games_won,total_shots,shot_highscore,single_game_shots,member_number'],
            'criteria_value' => ['integer', 'min:0'],
            'exact_match' => ['boolean'],
            'character_id' => ['nullable', 'integer', 'exists:characters,id'],
            'available_from' => ['nullable', 'date'],
            'available_until' => ['nullable', 'date'],
        ];
    }
}
