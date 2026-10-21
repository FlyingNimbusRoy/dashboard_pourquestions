<?php

namespace App\Models;

use Database\Factories\GamePresetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'name', 'rounds', 'is_nsfw', 'pack_ids'])]
class GamePreset extends Model
{
    /** @use HasFactory<GamePresetFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'rounds' => 'integer',
            'is_nsfw' => 'boolean',
            'pack_ids' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
