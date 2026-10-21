<?php

namespace App\Services\Crud;

use App\Models\Category;
use App\Services\Questions\QuestionMover;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/** @extends CrudService<Category> */
class CategoryService extends CrudService
{
    protected string $model = Category::class;

    protected array $searchable = ['name', 'description'];

    public function __construct(private readonly QuestionMover $mover) {}

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query->withCount('questions');
    }

    protected function beforeDelete(Model $model): void
    {
        if ($model->questions()->exists()) {
            throw ValidationException::withMessages(['category' => 'Category still has questions; move or merge them first.']);
        }
    }

    /** @return int number of questions moved */
    public function merge(Category $from, Category $into): int
    {
        return $this->mover->mergeCategory($from, $into);
    }
}
