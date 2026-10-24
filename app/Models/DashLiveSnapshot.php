<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashLiveSnapshot extends Model
{
    public $timestamps = false;

    protected $table = 'dash_live_snapshots';

    protected $fillable = [
        'open_rooms',
        'players_online',
        'games_today',
        'rooms_per_phase',
        'failed_post_count',
        'retry_queue_size',
        'snapshot_at',
    ];

    protected $casts = [
        'rooms_per_phase' => 'array',
        'snapshot_at'     => 'datetime',
    ];
}
