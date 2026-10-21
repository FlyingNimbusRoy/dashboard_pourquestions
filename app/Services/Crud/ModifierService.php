<?php

namespace App\Services\Crud;

use App\Models\Modifier;
use Illuminate\Database\Eloquent\Builder;

/** @extends CrudService<Modifier> */
class ModifierService extends CrudService
{
    protected string $model = Modifier::class;

    protected array $with = ['gamepack'];

    protected array $searchable = ['name', 'description'];

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (filled($filters['gamepack_id'] ?? null)) {
            $query->where('coupled_gamepack_id', (int) $filters['gamepack_id']);
        }
        foreach (['is_active', 'turnbased'] as $flag) {
            if (isset($filters[$flag]) && $filters[$flag] !== '') {
                $query->where($flag, filter_var($filters[$flag], FILTER_VALIDATE_BOOLEAN));
            }
        }

        return $query;
    }
}
