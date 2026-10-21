<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Question */
class QuestionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'maker_id' => $this->maker_id,
            'vraag' => $this->vraag,
            'trivia' => $this->trivia,
            'is_random' => $this->is_random,
            'is_nsfw' => $this->is_nsfw,
            'category_id' => $this->category_id,
            'gamepack_id' => $this->gamepack_id,
            'difficulty' => $this->difficulty,
            'last_validated' => $this->last_validated,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'gamepack' => GamepackResource::make($this->whenLoaded('gamepack')),
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),
            'validation_due' => $this->isValidationDue(),
            'ever_validated' => $this->wasEverValidated(),
        ];
    }
}
