<?php

namespace App\Http\Requests\SetupPreset;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSetupPresetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'name' => ['sometimes', 'string', 'max:24'],
            'game_length' => ['sometimes', 'integer', 'min:1', 'max:255'],
            'is_nsfw' => ['sometimes', 'boolean'],
            'burst_mode' => ['sometimes', 'boolean'],
            'excluded_packs' => ['sometimes', 'nullable', 'array'],
            'excluded_packs.*' => ['integer'],
            'excluded_categories' => ['sometimes', 'nullable', 'array'],
            'excluded_categories.*' => ['integer'],
        ];
    }
}
