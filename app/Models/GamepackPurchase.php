<?php

namespace App\Models;

use Database\Factories\GamepackPurchaseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('gamepack_purchased')]
#[Fillable(['gamepack_id', 'user_id', 'opened', 'access_through_admin_grant'])]
class GamepackPurchase extends Model
{

    /** @use HasFactory<GamepackPurchaseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'opened' => 'boolean',
            'access_through_admin_grant' => 'boolean',
        ];
    }

    /** @return BelongsTo<Gamepack, $this> */
    public function gamepack(): BelongsTo
    {
        return $this->belongsTo(Gamepack::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
