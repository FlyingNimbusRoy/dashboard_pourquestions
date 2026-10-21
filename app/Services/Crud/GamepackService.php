<?php

namespace App\Services\Crud;

use App\Models\Gamepack;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/** @extends CrudService<Gamepack> */
class GamepackService extends CrudService
{
    protected string $model = Gamepack::class;

    protected array $searchable = ['name', 'description'];

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (filled($filters['category'] ?? null)) {
            $query->where('category', $filters['category']);
        }

        return $query->withCount('questions');
    }

    protected function beforeDelete(Model $model): void
    {
        if ($model->questions()->exists() || $model->characters()->exists() || $model->modifiers()->exists()) {
            throw ValidationException::withMessages(['gamepack' => 'Gamepack still has questions, characters or modifiers.']);
        }
    }
}
