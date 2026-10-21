<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Gamepack */
class GamepackResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'price' => $this->price,
            'url_coverart' => $this->url_coverart,
            'flavor_text' => $this->flavor_text,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'questions_count' => $this->whenCounted('questions'),
        ];
    }
}
