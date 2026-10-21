<?php

namespace App\Models;

use Database\Factories\UserGamestatsFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One row of lifetime statistics per player. */
#[Table('user_gamestats')]
#[Fillable([
    'user_id', 'games_played', 'games_won', 'games_lost', 'total_shots', 'most_shots', 'least_shots',
    'shot_highscore', 'question_highscore', 'modifiers',
])]
class UserGamestats extends Model
{
    /** @use HasFactory<UserGamestatsFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'modifiers' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
