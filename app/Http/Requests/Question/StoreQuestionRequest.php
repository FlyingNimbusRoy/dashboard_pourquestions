<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
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
            'vraag' => [$req, 'string', 'max:255'],
            'trivia' => ['nullable', 'string', 'max:255'],
            'maker_id' => ['sometimes', 'integer', 'exists:users,id'],
            'is_random' => ['sometimes', 'boolean'],
            'is_nsfw' => ['sometimes', 'boolean'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'gamepack_id' => ['nullable', 'integer', 'exists:gamepacks,id'],
            'difficulty' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'last_validated' => ['sometimes', 'date'],
            'answers' => [$req, 'array', 'min:1'],
            'answers.*.id' => ['nullable', 'integer'],
            'answers.*.answer' => ['required', 'string', 'max:255'],
            'answers.*.is_correct' => ['required', 'boolean'],
        ];
    }
}
