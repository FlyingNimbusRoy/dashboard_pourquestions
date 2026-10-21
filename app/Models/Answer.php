<?php

namespace App\Models;

use App\Services\LiveSync\RecordsLiveChanges;
use App\Observers\AnswerObserver;
use Database\Factories\AnswerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Touches;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Touching the question marks it as changed, so the similarity check re-compares it.
#[Fillable(['question_id', 'answer', 'is_correct'])]
#[ObservedBy(AnswerObserver::class)]
#[Touches('question')]
class Answer extends Model
{
    use RecordsLiveChanges;

    /** @use HasFactory<AnswerFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    /** @return BelongsTo<Question, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
