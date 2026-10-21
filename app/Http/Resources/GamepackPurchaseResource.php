<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GamepackPurchaseResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'gamepack_id' => $this->gamepack_id,
            'opened' => $this->opened,
            'access_through_admin_grant' => $this->access_through_admin_grant,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
