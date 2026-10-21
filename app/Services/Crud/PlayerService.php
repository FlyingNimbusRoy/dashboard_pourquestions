<?php

namespace App\Services\Crud;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Players are the game's users. They come from the game: the dashboard may only change
 * name / is_admin. Email, password and remember_token are never accepted or exposed.
 */
class PlayerService extends CrudService
{
    protected string $model = User::class;

    protected array $with = ['gamestats'];

    protected array $searchable = ['name'];

    public function query(): Builder
    {
        return parent::query()->withCount('achievements');
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['is_admin']) && $filters['is_admin'] !== '') {
            $query->where('is_admin', filter_var($filters['is_admin'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }

    public function create(array $data): Model
    {
        throw ValidationException::withMessages(['player' => 'Players come from the game and cannot be created here.']);
    }

    public function delete(Model $model): void
    {
        throw ValidationException::withMessages(['player' => 'Players cannot be deleted from the dashboard.']);
    }

    protected function beforeSave(Model $model, array $data): array
    {
        return array_intersect_key($data, array_flip(['name', 'is_admin']));
    }
}
