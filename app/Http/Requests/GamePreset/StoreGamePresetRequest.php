<?php

namespace App\Http\Requests\GamePreset;

use Illuminate\Foundation\Http\FormRequest;

class StoreGamePresetRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'rounds' => ['integer', 'min:1', 'max:255'],
            'is_nsfw' => ['boolean'],
            'pack_ids' => ['required', 'array'],
            'pack_ids.*' => ['integer'],
        ];
    }
}
