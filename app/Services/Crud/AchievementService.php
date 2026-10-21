<?php

namespace App\Services\Crud;

use App\Models\Achievement;
use Illuminate\Database\Eloquent\Builder;

class AchievementService extends CrudService
{
    protected string $model = Achievement::class;

    protected array $searchable = ['name', 'description'];

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (filled($filters['character_id'] ?? null)) {
            $query->where('character_id', $filters['character_id']);
        }
        if (filled($filters['criteria_type'] ?? null)) {
            $query->where('criteria_type', $filters['criteria_type']);
        }
        if (isset($filters['exact_match']) && $filters['exact_match'] !== '') {
            $query->where('exact_match', filter_var($filters['exact_match'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
