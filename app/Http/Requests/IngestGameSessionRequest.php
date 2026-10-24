<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IngestGameSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access already gated by ValidateIngestSecret middleware
    }

    public function rules(): array
    {
        return [
            'schema_version' => ['required', 'integer', 'min:1'],
            'app_version'    => ['required', 'string', 'max:20'],
            'game_id'        => ['required', 'integer', 'min:1'],
            'pin'            => ['required', 'string', 'max:10'],
            'host_user_id'   => ['nullable', 'integer'],
            'host_device_id' => ['nullable', 'string', 'max:64'],
            'host_device'    => ['nullable', 'array'],
            'status'         => ['required', 'in:completed,ended_early,abandoned'],
            'started_at'     => ['nullable', 'date'],
            'ended_at'       => ['nullable', 'date'],
            'settings'       => ['required', 'array'],
            'lead_changes'   => ['nullable', 'integer', 'min:0'],
            'last_phase'     => ['nullable', 'array'],
            'players'        => ['nullable', 'array'],
            'questions'      => ['nullable', 'array'],
            'questions.*.id'           => ['required', 'integer'],
            'questions.*.version'      => ['nullable', 'integer'],
            'questions.*.category_id'  => ['nullable', 'integer'],
            'questions.*.round'        => ['required', 'integer', 'min:1'],
            'questions.*.correct'      => ['nullable', 'integer', 'min:0'],
            'questions.*.wrong'        => ['nullable', 'integer', 'min:0'],
            'questions.*.timeout'      => ['nullable', 'integer', 'min:0'],
            'questions.*.option_counts' => ['nullable', 'array'],
            'minigames'  => ['nullable', 'array'],
            'phases'     => ['nullable', 'array'],
        ];
    }
}
