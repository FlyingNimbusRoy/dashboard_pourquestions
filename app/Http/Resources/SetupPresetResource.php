<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SetupPresetResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'game_length' => $this->game_length,
            'is_nsfw' => $this->is_nsfw,
            'burst_mode' => $this->burst_mode,
            'excluded_packs' => $this->excluded_packs,
            'excluded_categories' => $this->excluded_categories,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
