<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GamePresetResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'rounds' => $this->rounds,
            'is_nsfw' => $this->is_nsfw,
            'pack_ids' => $this->pack_ids,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
