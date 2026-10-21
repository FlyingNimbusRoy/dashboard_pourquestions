<?php

namespace App\Http\Requests\Modifier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateModifierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $req = 'sometimes';

        return [
            'name' => [$req, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:500'],
            'turnbased' => ['sometimes', 'boolean'],
            'effects' => ['nullable', 'array'],
            'coupled_gamepack_id' => ['nullable', 'integer', 'exists:gamepacks,id'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
