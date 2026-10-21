<?php

namespace App\Services\Validation;

use App\Enums\ReviewOutcome;
use App\Models\Question;
use App\Models\QuestionReview;
use App\Models\ReviewBatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Puts questions that are due for revalidation into review batches for human reviewers.
 * The longest-unvalidated questions go first; questions already waiting in an open batch,
 * or flagged as "needs fix" and not yet fixed, are never picked twice.
 */
class ReviewBatchPlanner
{
    /**
     * Creates one batch when fewer than the configured number of batches are open.
     * Used by the scheduler, so reviewers are never buried under unfinished batches.
     */
    public function scheduleBatch(): ?ReviewBatch
    {
        if (ReviewBatch::open()->count() >= (int) config('dashboard.validation.max_open_batches')) {
            return null;
        }

        return $this->createBatch();
    }

    /**
     * One-click start: continues the reviewer's oldest open batch that still has pending questions,
     * otherwise creates a new (small) batch for them. Null when nothing is due.
     */
    public function startBatch(User $reviewer): ?ReviewBatch
    {
        $existing = ReviewBatch::open()
            ->where('reviewer_id', $reviewer->id)
            ->whereHas('reviews', fn (Builder $q) => $q->where('outcome', ReviewOutcome::Pending))
            ->orderBy('id')
            ->first();

        return $existing ?? $this->createBatch($reviewer);
    }

    /**
     * @param  list<int>|null  $questionIds  explicit selection (e.g. a bulk action); due-date rules are skipped
     */
    public function createBatch(
        ?User $reviewer = null,
        ?int $size = null,
        ?int $categoryId = null,
        ?array $questionIds = null,
        ?string $name = null,
    ): ?ReviewBatch {
        $questions = $this->selectQuestions($size ?? (int) config('dashboard.validation.batch_size'), $categoryId, $questionIds);

        if ($questions->isEmpty()) {
            return null;
        }

        $reviewer ??= $this->nextReviewer();

        return DB::transaction(function () use ($questions, $reviewer, $name) {
            $batch = ReviewBatch::create([
                'name' => $name ?? 'Review '.now()->format('Y-m-d').' ('.$questions->count().' questions)',
                'reviewer_id' => $reviewer?->id,
                'due_at' => now()->addDays((int) config('dashboard.validation.batch_due_days')),
            ]);

            foreach ($questions as $question) {
                $batch->reviews()->create([
                    'question_id' => $question->id,
                    'difficulty_before' => $question->difficulty,
                ]);
            }

            return $batch;
        });
    }

    /** The reviewer with the fewest open batches, so work is spread evenly. */
    public function nextReviewer(): ?User
    {
        return User::reviewers()
            ->withCount(['reviewBatches as open_batches_count' => fn (Builder $query) => $query->whereNull('completed_at')])
            ->orderBy('open_batches_count')
            ->orderBy('id')
            ->first();
    }

    /**
     * @param  list<int>|null  $questionIds
     * @return \Illuminate\Database\Eloquent\Collection<int, Question>
     */
    private function selectQuestions(int $size, ?int $categoryId, ?array $questionIds)
    {
        $query = Question::query()
            ->whereNotIn('id', $this->blockedQuestionIds())
            ->when($categoryId, fn (Builder $q) => $q->where('category_id', $categoryId));

        if ($questionIds !== null) {
            return $query->whereIn('id', $questionIds)->orderBy('id')->get();
        }

        return $query->validationDue()->orderBy('last_validated')->orderBy('id')->limit($size)->get();
    }

    /** Questions waiting in an open batch or flagged and not yet fixed. */
    private function blockedQuestionIds(): Builder
    {
        return QuestionReview::query()
            ->select('question_id')
            ->where(fn (Builder $q) => $q
                ->where('outcome', ReviewOutcome::Pending)
                ->orWhere(fn (Builder $q) => $q->where('outcome', ReviewOutcome::NeedsFix)->whereNull('resolved_at')));
    }
}
