<?php

namespace App\Services\Crud;

use App\Models\Answer;
use Illuminate\Database\Eloquent\Builder;

/** @extends CrudService<Answer> */
class AnswerService extends CrudService
{
    protected string $model = Answer::class;

    protected array $searchable = ['answer'];

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (filled($filters['question_id'] ?? null)) {
            $query->where('question_id', (int) $filters['question_id']);
        }
        if (isset($filters['is_correct']) && $filters['is_correct'] !== '') {
            $query->where('is_correct', filter_var($filters['is_correct'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
