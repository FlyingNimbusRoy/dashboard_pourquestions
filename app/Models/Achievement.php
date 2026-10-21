<?php

namespace App\Models;

use App\Services\LiveSync\RecordsLiveChanges;
use App\Enums\AchievementCriteria;
use Database\Factories\AchievementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'image', 'criteria_type', 'criteria_value', 'exact_match', 'character_id', 'available_from', 'available_until'])]
class Achievement extends Model
{
    use RecordsLiveChanges;

    /** @use HasFactory<AchievementFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'criteria_type' => AchievementCriteria::class,
            'criteria_value' => 'integer',
            'exact_match' => 'boolean',
            'available_from' => 'datetime',
            'available_until' => 'datetime',
        ];
    }

    /** @return BelongsTo<Character, $this> */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /** @return HasMany<UserAchievement, $this> */
    public function unlocks(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievements')->withPivot('unlocked_at')->withTimestamps();
    }
}
