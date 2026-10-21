<?php

namespace App\Models;

use App\Enums\SimilarityStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('dash_question_similarities')]
#[Fillable(['question_id', 'similar_question_id', 'score', 'signals', 'status', 'reviewed_by', 'reviewed_at'])]
class QuestionSimilarity extends Model
{
    protected function casts(): array
    {
        return [
            'score' => 'float',
            'signals' => 'array',
            'status' => SimilarityStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Question, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /** @return BelongsTo<Question, $this> */
    public function similarQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'similar_question_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isLikelyDuplicate(): bool
    {
        return $this->score >= (float) config('dashboard.similarity.duplicate_threshold');
    }
}
