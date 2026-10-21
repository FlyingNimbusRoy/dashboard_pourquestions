<?php

namespace App\Http\Requests\BoozeBuddy;

use Illuminate\Foundation\Http\FormRequest;

class StoreBoozeBuddyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'sender_id' => ['required', 'integer', 'exists:users,id'],
            'receiver_id' => ['required', 'integer', 'different:sender_id', 'exists:users,id'],
            'status' => ['in:pending,accepted,declined'],
        ];
    }
}
