<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'is_nsfw' => ['sometimes', 'boolean'],
            'is_finished' => ['sometimes', 'boolean'],
            'is_cancelled' => ['sometimes', 'boolean'],
        ];
    }
}
