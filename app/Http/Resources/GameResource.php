<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'shotstaken' => $this->shotstaken,
            'players' => $this->players,
            'game_length' => $this->game_length,
            'is_nsfw' => $this->is_nsfw,
            'is_finished' => $this->is_finished,
            'is_cancelled' => $this->is_cancelled,
            'host_id' => $this->host_id,
            'sAndb' => $this->sAndb,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'host' => $this->whenLoaded('host', fn () => ['id' => $this->host->id, 'name' => $this->host->name]),
        ];
    }
}
