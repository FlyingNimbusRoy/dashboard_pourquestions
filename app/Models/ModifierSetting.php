<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Settings for a socket-server modifier (MOD key), optionally linked to a game `modifiers` row (D-006). */
#[Table('dash_modifier_settings')]
#[Fillable(['key', 'name', 'description', 'turnbased', 'modifier_id', 'is_active', 'gamepack_id', 'settings'])]
class ModifierSetting extends Model
{
    protected function casts(): array
    {
        return [
            'turnbased' => 'boolean',
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    /** @return BelongsTo<Modifier, $this> */
    public function modifier(): BelongsTo
    {
        return $this->belongsTo(Modifier::class);
    }

    /** @return BelongsTo<Gamepack, $this> */
    public function gamepack(): BelongsTo
    {
        return $this->belongsTo(Gamepack::class);
    }
}
