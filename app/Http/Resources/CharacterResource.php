<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Character */
class CharacterResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'gamepack_id' => $this->gamepack_id,
            'parent_character_id' => $this->parent_character_id,
            'show_on_homepage' => $this->show_on_homepage,
            'color_primary' => $this->color_primary,
            'color_secondary' => $this->color_secondary,
            'color_muted_primary' => $this->color_muted_primary,
            'color_muted_secondary' => $this->color_muted_secondary,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'gamepack' => GamepackResource::make($this->whenLoaded('gamepack')),
        ];
    }
}
