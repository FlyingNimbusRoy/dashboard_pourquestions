<?php

namespace App\Models;

use App\Enums\BuddyStatus;
use Database\Factories\BoozeBuddyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A friend request between two players (`status` tells whether it was accepted). */
#[Table('booze_buddies')]
#[Fillable(['sender_id', 'receiver_id', 'status'])]
class BoozeBuddy extends Model
{
    /** @use HasFactory<BoozeBuddyFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => BuddyStatus::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /** @return BelongsTo<User, $this> */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
