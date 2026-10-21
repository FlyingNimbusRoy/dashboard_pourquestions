<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Answer */
class AnswerResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question_id' => $this->question_id,
            'answer' => $this->answer,
            'is_correct' => $this->is_correct,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
