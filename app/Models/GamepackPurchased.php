<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamepackPurchased extends Model
{
    protected $table = 'gamepack_purchased';

    protected $fillable = [
        'gamepack_id',
        'user_id',
        'opened',
        'access_through_admin_grant',
    ];

    protected $casts = [
        'access_through_admin_grant' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function gamepack()
    {
        return $this->belongsTo(Gamepack::class, 'gamepack_id');
    }
}