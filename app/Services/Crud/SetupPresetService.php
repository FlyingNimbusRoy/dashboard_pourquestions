<?php

namespace App\Services\Crud;

use App\Models\SetupPreset;
use Illuminate\Database\Eloquent\Builder;

class SetupPresetService extends CrudService
{
    protected string $model = SetupPreset::class;

    protected array $searchable = ['name'];

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (filled($filters['user_id'] ?? null)) {
            $query->where('user_id', $filters['user_id']);
        }
        if (isset($filters['is_nsfw']) && $filters['is_nsfw'] !== '') {
            $query->where('is_nsfw', filter_var($filters['is_nsfw'], FILTER_VALIDATE_BOOLEAN));
        }
        if (isset($filters['burst_mode']) && $filters['burst_mode'] !== '') {
            $query->where('burst_mode', filter_var($filters['burst_mode'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
