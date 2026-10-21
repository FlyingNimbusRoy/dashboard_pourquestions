<?php

namespace App\Services\Crud;

use App\Models\Minigame;
use Illuminate\Database\Eloquent\Builder;

/** @extends CrudService<Minigame> */
class MinigameService extends CrudService
{
    protected string $model = Minigame::class;

    protected array $with = ['gamepack'];

    protected array $searchable = ['key', 'name', 'description'];

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (filled($filters['gamepack_id'] ?? null)) {
            $query->where('gamepack_id', (int) $filters['gamepack_id']);
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
