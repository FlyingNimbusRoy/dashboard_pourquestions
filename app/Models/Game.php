<?php

namespace App\Models;

use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A game session. `question_array` holds the question ids drawn for it; `sAndb` is a game-owned column. */
#[Fillable([
    'name', 'shotstaken', 'players', 'game_length', 'is_nsfw', 'is_finished', 'is_cancelled',
    'host_id', 'sAndb', 'question_array',
])]
class Game extends Model
{

    /** @use HasFactory<GameFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'shotstaken' => 'integer',
            'players' => 'integer',
            'game_length' => 'integer',
            'is_nsfw' => 'boolean',
            'is_finished' => 'boolean',
            'is_cancelled' => 'boolean',
            'question_array' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /** @return HasMany<OnlineAction, $this> */
    public function onlineActions(): HasMany
    {
        return $this->hasMany(OnlineAction::class);
    }

    /** @return HasMany<QuestionGameResult, $this> */
    public function questionResults(): HasMany
    {
        return $this->hasMany(QuestionGameResult::class);
    }

    /** @return HasMany<PlayerGameLog, $this> */
    public function playerLog(): HasMany
    {
        return $this->hasMany(PlayerGameLog::class);
    }
}
