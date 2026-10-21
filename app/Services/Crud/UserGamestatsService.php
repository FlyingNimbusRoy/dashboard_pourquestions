<?php

namespace App\Services\Crud;

use App\Models\UserGamestats;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UserGamestatsService extends CrudService
{
    protected string $model = UserGamestats::class;

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (filled($filters['user_id'] ?? null)) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query;
    }

    public function create(array $data): Model
    {
        throw ValidationException::withMessages(['UserGamestats' => 'Created by the game itself; cannot be created from the dashboard.']);
    }

    public function delete(Model $model): void
    {
        throw ValidationException::withMessages(['UserGamestats' => 'Cannot be deleted from the dashboard.']);
    }
}
