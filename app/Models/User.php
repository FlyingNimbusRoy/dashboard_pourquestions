<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Players and admins share the game's `users` table; only admins (`is_admin`) can use
 * the dashboard, and every admin can act as a question reviewer.
 */
#[Fillable(['name', 'email', 'password', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }

    public function scopeReviewers(Builder $query): Builder
    {
        return $query->where('is_admin', true);
    }

    /** @return HasMany<ReviewBatch, $this> */
    public function reviewBatches(): HasMany
    {
        return $this->hasMany(ReviewBatch::class, 'reviewer_id');
    }

    /** @return HasMany<Question, $this> */
    public function madeQuestions(): HasMany
    {
        return $this->hasMany(Question::class, 'maker_id');
    }

    /** @return HasMany<Game, $this> */
    public function hostedGames(): HasMany
    {
        return $this->hasMany(Game::class, 'host_id');
    }

    /** @return HasOne<UserGamestats, $this> */
    public function gamestats(): HasOne
    {
        return $this->hasOne(UserGamestats::class);
    }

    /** @return HasMany<UserAchievement, $this> */
    public function achievementUnlocks(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    /** @return BelongsToMany<Achievement, $this> */
    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')->withPivot('unlocked_at')->withTimestamps();
    }

    /** @return HasMany<GamepackPurchase, $this> */
    public function gamepackPurchases(): HasMany
    {
        return $this->hasMany(GamepackPurchase::class);
    }

    /** @return BelongsToMany<Gamepack, $this> */
    public function gamepacks(): BelongsToMany
    {
        return $this->belongsToMany(Gamepack::class, 'gamepack_purchased')
            ->withPivot(['opened', 'access_through_admin_grant'])->withTimestamps();
    }

    /** @return HasMany<BoozeBuddy, $this> */
    public function sentBuddyRequests(): HasMany
    {
        return $this->hasMany(BoozeBuddy::class, 'sender_id');
    }

    /** @return HasMany<BoozeBuddy, $this> */
    public function receivedBuddyRequests(): HasMany
    {
        return $this->hasMany(BoozeBuddy::class, 'receiver_id');
    }

    /** @return HasMany<GamePreset, $this> */
    public function gamePresets(): HasMany
    {
        return $this->hasMany(GamePreset::class);
    }

    /** @return HasMany<SetupPreset, $this> */
    public function setupPresets(): HasMany
    {
        return $this->hasMany(SetupPreset::class);
    }
}
