<?php

namespace App\Services\Crud;

use App\Models\BoozeBuddy;
use Illuminate\Database\Eloquent\Builder;

class BoozeBuddyService extends CrudService
{
    protected string $model = BoozeBuddy::class;

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (filled($filters['sender_id'] ?? null)) {
            $query->where('sender_id', $filters['sender_id']);
        }
        if (filled($filters['receiver_id'] ?? null)) {
            $query->where('receiver_id', $filters['receiver_id']);
        }
        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }
}
