<?php

namespace App\Models;

use App\Casts\GameEffects;
use App\Services\LiveSync\RecordsLiveChanges;
use Database\Factories\ModifierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['name', 'description', 'icon', 'turnbased', 'effects', 'coupled_gamepack_id', 'is_active'])]
class Modifier extends Model
{
    use RecordsLiveChanges;

    /** @use HasFactory<ModifierFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'turnbased' => 'boolean',
            'is_active' => 'boolean',
            'effects' => GameEffects::class,
        ];
    }

    /** @return BelongsTo<Gamepack, $this> */
    public function gamepack(): BelongsTo
    {
        return $this->belongsTo(Gamepack::class, 'coupled_gamepack_id');
    }

    /** @return HasOne<ModifierSetting, $this> Socket settings (local dash_ table, D-006). */
    public function socketSetting(): HasOne
    {
        return $this->hasOne(ModifierSetting::class);
    }
}
