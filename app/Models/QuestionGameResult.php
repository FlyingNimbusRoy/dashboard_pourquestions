<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Per-question correct/wrong/timeout totals for a completed game. */
#[Fillable(['question_id', 'game_id', 'answered_correct', 'answered_wrong', 'answered_timeout'])]
class QuestionGameResult extends Model
{
    /** @return BelongsTo<Question, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /** @return BelongsTo<Game, $this> */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function totalAnswers(): int
    {
        return $this->answered_correct + $this->answered_wrong + $this->answered_timeout;
    }

    public function correctRate(): float
    {
        $total = $this->totalAnswers();

        return $total > 0 ? round($this->answered_correct / $total, 4) : 0.0;
    }
}
