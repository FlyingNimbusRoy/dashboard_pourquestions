<?php

namespace App\Http\Requests\BoozeBuddy;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBoozeBuddyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'sender_id' => ['sometimes', 'integer', 'exists:users,id'],
            'receiver_id' => ['sometimes', 'integer', 'different:sender_id', 'exists:users,id'],
            'status' => ['sometimes', 'in:pending,accepted,declined'],
        ];
    }
}
