<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Socket-server minigame registry entry (local dash_ table, never part of the live export, D-006). */
#[Table('dash_minigames')]
#[Fillable(['key', 'name', 'description', 'gamepack_id', 'is_active', 'min_players', 'max_players', 'settings', 'sort'])]
class Minigame extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    /** @return BelongsTo<Gamepack, $this> */
    public function gamepack(): BelongsTo
    {
        return $this->belongsTo(Gamepack::class);
    }
}
