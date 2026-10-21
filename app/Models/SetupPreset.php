<?php

namespace App\Models;

use Database\Factories\SetupPresetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'name', 'game_length', 'is_nsfw', 'burst_mode', 'excluded_packs', 'excluded_categories'])]
class SetupPreset extends Model
{
    /** @use HasFactory<SetupPresetFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'game_length' => 'integer',
            'is_nsfw' => 'boolean',
            'burst_mode' => 'boolean',
            'excluded_packs' => 'array',
            'excluded_categories' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
