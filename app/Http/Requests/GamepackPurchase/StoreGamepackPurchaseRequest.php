<?php

namespace App\Http\Requests\GamepackPurchase;

use Illuminate\Foundation\Http\FormRequest;

class StoreGamepackPurchaseRequest extends FormRequest
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
            'gamepack_id' => ['required', 'integer', 'exists:gamepacks,id'],
            'opened' => ['boolean'],
        ];
    }
}
