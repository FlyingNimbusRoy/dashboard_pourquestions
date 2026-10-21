<?php

namespace App\Models;

use App\Enums\DenyReason;
use App\Enums\ReviewOutcome;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('dash_question_reviews')]
#[Fillable([
    'review_batch_id', 'question_id', 'reviewer_id', 'outcome', 'difficulty_before', 'difficulty_rating',
    'answers_correct', 'wording_clear', 'category_correct', 'notes', 'reason', 'reviewed_at', 'resolved_at',
])]
class QuestionReview extends Model
{
    protected function casts(): array
    {
        return [
            'outcome' => ReviewOutcome::class,
            'reason' => DenyReason::class,
            'answers_correct' => 'boolean',
            'wording_clear' => 'boolean',
            'category_correct' => 'boolean',
            'reviewed_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<ReviewBatch, $this> */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(ReviewBatch::class, 'review_batch_id');
    }

    /** @return BelongsTo<Question, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
