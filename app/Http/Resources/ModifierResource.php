<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Modifier */
class ModifierResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'turnbased' => $this->turnbased,
            'effects' => $this->effects,
            'coupled_gamepack_id' => $this->coupled_gamepack_id,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'gamepack' => GamepackResource::make($this->whenLoaded('gamepack')),
        ];
    }
}
