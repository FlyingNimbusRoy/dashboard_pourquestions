<?php

namespace App\Models;

use App\Services\LiveSync\RecordsLiveChanges;
use App\Enums\GamepackCategory;
use Database\Factories\GamepackFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'category', 'description', 'icon', 'color', 'price', 'url_coverart', 'flavor_text'])]
class Gamepack extends Model
{
    use RecordsLiveChanges;

    /** @use HasFactory<GamepackFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'category' => GamepackCategory::class,
            'price' => 'decimal:2',
        ];
    }

    /** @return HasMany<Question, $this> */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /** @return HasMany<Character, $this> */
    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    /** @return HasMany<Modifier, $this> */
    public function modifiers(): HasMany
    {
        return $this->hasMany(Modifier::class, 'coupled_gamepack_id');
    }

    /** @return HasMany<GamepackPurchase, $this> */
    public function purchases(): HasMany
    {
        return $this->hasMany(GamepackPurchase::class);
    }

    /** @return HasMany<Minigame, $this> */
    public function minigames(): HasMany
    {
        return $this->hasMany(Minigame::class);
    }
}
