<?php

namespace App\Services\Validation;

use App\Enums\DenyReason;
use App\Enums\ReviewOutcome;
use App\Models\QuestionReview;
use App\Models\ReviewBatch;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The single place that applies review outcomes. Only an approval (or a re-validated denial)
 * renews the question's `last_validated` date and applies the reviewed difficulty.
 *
 * Vocabulary: "denied" = ReviewOutcome::NeedsFix. A denied review stays in the denied bin until it
 * is resolved (`resolved_at`): either re-validated, or sent back into a new review batch.
 */
class ReviewService
{
    /** Approve: the reviewer confirms answers, wording and category are right. */
    public function approve(QuestionReview $review, User $reviewer, ?int $difficulty = null): void
    {
        $this->assertPending($review);

        DB::transaction(function () use ($review, $reviewer, $difficulty) {
            $review->update([
                'outcome' => ReviewOutcome::Approved,
                'reviewer_id' => $reviewer->id,
                'difficulty_rating' => $difficulty ?? $review->difficulty_before,
                'answers_correct' => true,
                'wording_clear' => true,
                'category_correct' => true,
                'reviewed_at' => now(),
            ]);

            $this->validateQuestion($review);
            $review->batch->completeIfDone();
        });
    }

    /** Deny: the question goes to the denied bin with a reason (and an optional note). */
    public function deny(QuestionReview $review, User $reviewer, DenyReason $reason, ?string $note = null, ?int $difficulty = null): void
    {
        $this->assertPending($review);

        DB::transaction(function () use ($review, $reviewer, $reason, $note, $difficulty) {
            $review->update([
                'outcome' => ReviewOutcome::NeedsFix,
                'reviewer_id' => $reviewer->id,
                'difficulty_rating' => $difficulty ?? $review->difficulty_before,
                'answers_correct' => $reason === DenyReason::WrongAnswer ? false : null,
                'wording_clear' => $reason === DenyReason::UnclearWording ? false : null,
                'category_correct' => $reason === DenyReason::WrongCategory ? false : null,
                'reason' => $reason,
                'notes' => filled($note) ? trim($note) : null,
                'reviewed_at' => now(),
            ]);

            $review->batch->completeIfDone();
        });
    }

    /** Leaves the question unreviewed; it stays due and can be picked for a later batch. */
    public function skip(QuestionReview $review, User $reviewer): void
    {
        if ($review->outcome !== ReviewOutcome::Pending) {
            return;
        }

        DB::transaction(function () use ($review, $reviewer) {
            $review->update(['outcome' => ReviewOutcome::Skipped, 'reviewer_id' => $reviewer->id, 'reviewed_at' => now()]);
            $review->batch->completeIfDone();
        });
    }

    /** Resolves a denied review once the question has been corrected: renews the validation. */
    public function resolve(QuestionReview $review): void
    {
        if ($review->outcome !== ReviewOutcome::NeedsFix || $review->resolved_at !== null) {
            return;
        }

        DB::transaction(function () use ($review) {
            $review->update(['resolved_at' => now()]);
            $this->validateQuestion($review);
        });
    }

    /**
     * Takes denied reviews out of the bin and puts their questions into a fresh batch for $reviewer,
     * without renewing their validation.
     *
     * @param  Collection<int, QuestionReview>  $reviews
     */
    public function sendBack(Collection $reviews, User $reviewer): ?ReviewBatch
    {
        $open = $reviews->filter(fn (QuestionReview $r) => $r->outcome === ReviewOutcome::NeedsFix && $r->resolved_at === null);

        if ($open->isEmpty()) {
            return null;
        }

        return DB::transaction(function () use ($open, $reviewer) {
            QuestionReview::whereIn('id', $open->modelKeys())->update(['resolved_at' => now()]);

            return app(ReviewBatchPlanner::class)->createBatch(
                reviewer: $reviewer,
                questionIds: $open->pluck('question_id')->all(),
                name: 'Re-check '.now()->format('Y-m-d H:i').' ('.$open->count().' questions)',
            );
        });
    }

    private function assertPending(QuestionReview $review): void
    {
        if ($review->outcome !== ReviewOutcome::Pending) {
            throw ValidationException::withMessages(['outcome' => 'This question has already been reviewed in this batch.']);
        }
    }

    private function validateQuestion(QuestionReview $review): void
    {
        $review->question->update(array_filter([
            'difficulty' => $review->difficulty_rating,
            'last_validated' => now(),
        ], fn ($value) => $value !== null));
    }
}
