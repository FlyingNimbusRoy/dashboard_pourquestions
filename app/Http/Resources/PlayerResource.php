<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_admin' => $this->is_admin,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'gamestats' => $this->whenLoaded('gamestats', fn () => new UserGamestatsResource($this->gamestats)),
            'achievements_count' => $this->whenCounted('achievements'),
        ];
    }
}
