<?php

namespace App\Services\Crud;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/** @extends CrudService<Question> */
class QuestionService extends CrudService
{
    protected string $model = Question::class;

    protected array $with = ['category', 'gamepack', 'answers'];

    protected array $searchable = ['vraag', 'trivia'];

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (filled($filters['category_id'] ?? null)) {
            $query->where('category_id', (int) $filters['category_id']);
        }
        if (filter_var($filters['uncategorized'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $query->whereNull('category_id');
        }
        if (filled($filters['gamepack_id'] ?? null)) {
            $query->where('gamepack_id', (int) $filters['gamepack_id']);
        }
        if (filled($filters['difficulty'] ?? null)) {
            $query->where('difficulty', (int) $filters['difficulty']);
        }
        if (isset($filters['is_nsfw']) && $filters['is_nsfw'] !== '') {
            $query->where('is_nsfw', filter_var($filters['is_nsfw'], FILTER_VALIDATE_BOOLEAN));
        }
        match ($filters['validation'] ?? null) {
            'never' => $query->where('last_validated', '<=', Question::NEVER_VALIDATED),
            'due' => $query->validationDue(),
            'valid' => $query->where('last_validated', '>=', now()->subDays((int) config('dashboard.validation.revalidate_after_days'))),
            default => null,
        };

        return $query;
    }

    protected function beforeSave(Model $model, array $data): array
    {
        if (array_key_exists('answers', $data) && ! collect($data['answers'])->contains(fn ($a) => ! empty($a['is_correct']))) {
            throw ValidationException::withMessages(['answers' => 'At least one answer must be correct.']);
        }
        unset($data['answers']);
        if (! $model->exists && ! isset($data['maker_id'])) {
            $data['maker_id'] = auth()->id();
        }

        return $data;
    }

    protected function afterSave(Model $model, array $data): void
    {
        if (! array_key_exists('answers', $data)) {
            return;
        }

        $keep = [];
        foreach ($data['answers'] as $row) {
            $answer = ! empty($row['id']) ? $model->answers()->findOrFail($row['id']) : new Answer(['question_id' => $model->id]);
            $answer->fill(['answer' => $row['answer'], 'is_correct' => (bool) $row['is_correct']])->save();
            $keep[] = $answer->id;
        }
        $model->answers()->whereNotIn('id', $keep)->get()->each->delete();
    }

    /** Stamps the question as reviewed-and-confirmed today. */
    public function validate(Question $question): void
    {
        $question->last_validated = now();
        $question->save();
    }
}
