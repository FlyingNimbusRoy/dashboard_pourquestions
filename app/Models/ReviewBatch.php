<?php

namespace App\Models;

use App\Enums\ReviewOutcome;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('dash_review_batches')]
#[Fillable(['name', 'reviewer_id', 'due_at', 'completed_at'])]
class ReviewBatch extends Model
{
    protected function casts(): array
    {
        return [
            'due_at' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /** @return HasMany<QuestionReview, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(QuestionReview::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('completed_at');
    }

    public function isOverdue(): bool
    {
        return $this->completed_at === null && $this->due_at !== null && $this->due_at->endOfDay()->isPast();
    }

    public function nextPendingReview(): ?QuestionReview
    {
        return $this->reviews()->where('outcome', ReviewOutcome::Pending)->orderBy('id')->first();
    }

    /** Marks the batch complete once nothing is pending any more. */
    public function completeIfDone(): void
    {
        if ($this->completed_at === null && ! $this->reviews()->where('outcome', ReviewOutcome::Pending)->exists()) {
            $this->update(['completed_at' => now()]);
        }
    }
}
