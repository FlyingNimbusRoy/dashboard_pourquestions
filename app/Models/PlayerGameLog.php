<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One row per logged-in player per completed game — enables per-player trend analysis. */
#[Fillable(['user_id', 'game_id', 'shots', 'answered_correct', 'answered_wrong', 'won'])]
class PlayerGameLog extends Model
{
    protected function casts(): array
    {
        return [
            'won' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Game, $this> */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
