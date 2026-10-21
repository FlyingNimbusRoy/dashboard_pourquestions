<?php

namespace App\Services\Crud;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Builder;

class CommentService extends CrudService
{
    protected string $model = Comment::class;

    protected array $searchable = ['comment'];

    protected function applyFilters(Builder $query, array $filters): Builder
    {

        return $query;
    }
}
