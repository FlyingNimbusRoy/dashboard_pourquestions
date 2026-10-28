<?php

namespace App\Models;

use App\Observers\QuestionObserver;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A game question. The table belongs to the game app, so column names are kept as-is
 * (`vraag` is the question text, `last_validated` the last completed human review).
 */
#[Fillable(['maker_id', 'vraag', 'trivia', 'is_random', 'is_nsfw', 'category_id', 'gamepack_id', 'difficulty', 'last_validated'])]
#[ObservedBy(QuestionObserver::class)]
class Question extends Model
{

    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    /** The game's column default for `last_validated`: anything up to this means "never reviewed". */
    public const NEVER_VALIDATED = '2021-01-01 00:00:00';

    protected function casts(): array
    {
        return [
            'is_random' => 'boolean',
            'is_nsfw' => 'boolean',
            'difficulty' => 'integer',
            'last_compared_at' => 'datetime',
            'last_validated' => 'datetime',
        ];
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<Gamepack, $this> */
    public function gamepack(): BelongsTo
    {
        return $this->belongsTo(Gamepack::class);
    }

    /** @return BelongsTo<User, $this> */
    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    /** @return HasMany<Answer, $this> */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /** @return HasMany<QuestionReview, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(QuestionReview::class);
    }

    /** @return HasMany<QuestionChange, $this> */
    public function changes(): HasMany
    {
        return $this->hasMany(QuestionChange::class)->latest('id');
    }

    /** @return HasMany<QuestionGameResult, $this> */
    public function gameResults(): HasMany
    {
        return $this->hasMany(QuestionGameResult::class);
    }

    /** Questions whose last review is older than the revalidation period (or never reviewed). */
    public function scopeValidationDue(Builder $query): Builder
    {
        return $query->where('last_validated', '<', now()->subDays((int) config('dashboard.validation.revalidate_after_days')));
    }

    public function wasEverValidated(): bool
    {
        return $this->last_validated !== null && $this->last_validated->gt(self::NEVER_VALIDATED);
    }

    public function isValidationDue(): bool
    {
        return $this->last_validated === null
            || $this->last_validated->lt(now()->subDays((int) config('dashboard.validation.revalidate_after_days')));
    }

    /** @return list<string> */
    public function correctAnswerTexts(): array
    {
        return $this->answers->where('is_correct', true)->pluck('answer')->values()->all();
    }
}
