<?php

namespace App\Services\Crud;

use App\Models\Character;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** @extends CrudService<Character> */
class CharacterService extends CrudService
{
    protected string $model = Character::class;

    protected array $with = ['gamepack'];

    protected array $searchable = ['name'];

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (filled($filters['gamepack_id'] ?? null)) {
            $query->where('gamepack_id', (int) $filters['gamepack_id']);
        }
        if (isset($filters['show_on_homepage']) && $filters['show_on_homepage'] !== '') {
            $query->where('show_on_homepage', filter_var($filters['show_on_homepage'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }

    protected function beforeDelete(Model $model): void
    {
        // Children become top-level characters (no FK cascade in the game schema).
        $model->children()->get()->each->update(['parent_character_id' => null]);
    }
}
