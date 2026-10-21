<?php

namespace App\Services\Crud;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Base CRUD service. Controllers and Filament pages both go through a service, so every
 * create/update/delete runs the same rules (D-004). Subclasses set $model and may override
 * the hooks (query(), applyFilters(), beforeSave(), afterSave(), beforeDelete()).
 *
 * @template TModel of Model
 */
abstract class CrudService
{
    /** @var class-string<TModel> */
    protected string $model;

    /** @var list<string> Relations eager-loaded for list and find. */
    protected array $with = [];

    /** @var list<string> Columns searched by the `search` filter. */
    protected array $searchable = [];

    /** @return Builder<TModel> */
    public function query(): Builder
    {
        return $this->model::query()->with($this->with);
    }

    /**
     * @param  array<string, mixed>  $filters  `search`, `sort`, `direction`, plus subclass-specific filters
     * @return LengthAwarePaginator<int, TModel>
     */
    public function list(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->applyFilters($this->query(), $filters);

        if (filled($filters['search'] ?? null) && $this->searchable !== []) {
            $query->where(function (Builder $q) use ($filters) {
                foreach ($this->searchable as $column) {
                    $q->orWhere($column, 'like', '%'.$filters['search'].'%');
                }
            });
        }

        $sort = $filters['sort'] ?? (new $this->model)->getKeyName();
        $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($sort, $direction)->paginate($perPage);
    }

    /** @return TModel */
    public function find(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data  validated input
     * @return TModel
     */
    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $model = new $this->model;
            $model->fill($this->beforeSave($model, $data))->save();
            $this->afterSave($model, $data);

            return $model->refresh();
        });
    }

    /**
     * @param  TModel  $model
     * @param  array<string, mixed>  $data  validated input
     * @return TModel
     */
    public function update(Model $model, array $data): Model
    {
        return DB::transaction(function () use ($model, $data) {
            $model->fill($this->beforeSave($model, $data))->save();
            $this->afterSave($model, $data);

            return $model->refresh();
        });
    }

    /** @param  TModel  $model */
    public function delete(Model $model): void
    {
        DB::transaction(function () use ($model) {
            $this->beforeDelete($model);
            $model->delete();
        });
    }

    /**
     * @param  Builder<TModel>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<TModel>
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query;
    }

    /**
     * Last chance to adjust the attributes before they are filled (e.g. strip relation data).
     *
     * @param  TModel  $model
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function beforeSave(Model $model, array $data): array
    {
        return $data;
    }

    /**
     * Save related data (e.g. answers of a question) after the model itself is saved.
     *
     * @param  TModel  $model
     * @param  array<string, mixed>  $data
     */
    protected function afterSave(Model $model, array $data): void {}

    /** @param  TModel  $model */
    protected function beforeDelete(Model $model): void {}
}
