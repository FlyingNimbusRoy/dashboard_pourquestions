<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserGamestatsResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'games_played' => $this->games_played,
            'games_won' => $this->games_won,
            'games_lost' => $this->games_lost,
            'total_shots' => $this->total_shots,
            'most_shots' => $this->most_shots,
            'least_shots' => $this->least_shots,
            'shot_highscore' => $this->shot_highscore,
            'question_highscore' => $this->question_highscore,
            'modifiers' => $this->modifiers,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
