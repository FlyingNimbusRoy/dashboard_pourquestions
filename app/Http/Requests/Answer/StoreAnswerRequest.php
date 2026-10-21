<?php

namespace App\Http\Requests\Answer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnswerRequest extends FormRequest
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
            'question_id' => [$req, 'integer', 'exists:questions,id'],
            'answer' => [$req, 'string', 'max:255'],
            'is_correct' => ['sometimes', 'boolean'],
        ];
    }
}
