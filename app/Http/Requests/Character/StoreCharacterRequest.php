<?php

namespace App\Http\Requests\Character;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCharacterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $req = 'required';

        return [
            'name' => [$req, 'string', 'max:255'],
            'url' => [$req, 'string', 'max:255'],
            'gamepack_id' => ['nullable', 'integer', 'exists:gamepacks,id'],
            'parent_character_id' => ['nullable', 'integer', 'exists:characters,id'],
            'show_on_homepage' => ['sometimes', 'boolean'],
            'color_primary' => ['sometimes', 'string', 'max:255'],
            'color_secondary' => ['sometimes', 'string', 'max:255'],
            'color_muted_primary' => ['sometimes', 'string', 'max:255'],
            'color_muted_secondary' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
