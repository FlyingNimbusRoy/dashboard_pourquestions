<?php

namespace App\Http\Requests\SetupPreset;

use Illuminate\Foundation\Http\FormRequest;

class StoreSetupPresetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:24'],
            'game_length' => ['integer', 'min:1', 'max:255'],
            'is_nsfw' => ['boolean'],
            'burst_mode' => ['boolean'],
            'excluded_packs' => ['nullable', 'array'],
            'excluded_packs.*' => ['integer'],
            'excluded_categories' => ['nullable', 'array'],
            'excluded_categories.*' => ['integer'],
        ];
    }
}
