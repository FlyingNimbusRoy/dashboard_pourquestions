<?php

namespace App\Models;

use Database\Factories\OnlineActionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A queued action of an online game (table `online`). */
#[Table('online')]
#[Fillable(['game_id', 'host', 'user_csrf', 'action'])]
class OnlineAction extends Model
{
    /** @use HasFactory<OnlineActionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'host' => 'boolean',
        ];
    }

    /** @return BelongsTo<Game, $this> */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
