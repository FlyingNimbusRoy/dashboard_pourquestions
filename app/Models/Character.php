<?php

namespace App\Models;

use Database\Factories\CharacterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'url', 'gamepack_id', 'parent_character_id', 'show_on_homepage',
    'color_primary', 'color_secondary', 'color_muted_primary', 'color_muted_secondary',
])]
class Character extends Model
{

    /** @use HasFactory<CharacterFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'show_on_homepage' => 'boolean',
        ];
    }

    /** @return BelongsTo<Gamepack, $this> */
    public function gamepack(): BelongsTo
    {
        return $this->belongsTo(Gamepack::class);
    }

    /** @return BelongsTo<Character, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_character_id');
    }

    /** @return HasMany<Character, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_character_id');
    }

    /** @return HasMany<Achievement, $this> */
    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }
}
