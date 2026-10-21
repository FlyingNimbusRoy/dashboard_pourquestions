<?php

namespace App\Http\Requests\GamePreset;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGamePresetRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:100'],
            'rounds' => ['sometimes', 'integer', 'min:1', 'max:255'],
            'is_nsfw' => ['sometimes', 'boolean'],
            'pack_ids' => ['sometimes', 'array'],
            'pack_ids.*' => ['integer'],
        ];
    }
}
