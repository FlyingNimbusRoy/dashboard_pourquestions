<?php

namespace App\Http\Requests\Gamepack;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGamepackRequest extends FormRequest
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
            'category' => ['sometimes', Rule::in(['gamepack', 'questionpack', 'skinpack'])],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'min:0', 'max:9999999999999'],
            'url_coverart' => ['nullable', 'string', 'max:255'],
            'flavor_text' => ['nullable', 'string', 'max:255'],
        ];
    }
}
