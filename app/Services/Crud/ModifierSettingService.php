<?php

namespace App\Services\Crud;

use App\Models\ModifierSetting;
use Illuminate\Database\Eloquent\Builder;

/** @extends CrudService<ModifierSetting> */
class ModifierSettingService extends CrudService
{
    protected string $model = ModifierSetting::class;

    protected array $with = ['modifier', 'gamepack'];

    protected array $searchable = ['key', 'name', 'description'];

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (filled($filters['gamepack_id'] ?? null)) {
            $query->where('gamepack_id', (int) $filters['gamepack_id']);
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }
        if (isset($filters['unmatched']) && filter_var($filters['unmatched'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereNull('modifier_id');
        }

        return $query;
    }
}
