<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AchievementResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'criteria_type' => $this->criteria_type,
            'criteria_value' => $this->criteria_value,
            'exact_match' => $this->exact_match,
            'character_id' => $this->character_id,
            'available_from' => $this->available_from,
            'available_until' => $this->available_until,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
