<?php

namespace App\Services\Crud;

use App\Models\GamePreset;
use Illuminate\Database\Eloquent\Builder;

class GamePresetService extends CrudService
{
    protected string $model = GamePreset::class;

    protected array $searchable = ['name'];

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (filled($filters['user_id'] ?? null)) {
            $query->where('user_id', $filters['user_id']);
        }
        if (isset($filters['is_nsfw']) && $filters['is_nsfw'] !== '') {
            $query->where('is_nsfw', filter_var($filters['is_nsfw'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
