<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashRollupDaily extends Model
{
    public $timestamps = false;

    protected $table = 'dash_rollup_daily';

    protected $fillable = [
        'date',
        'games_total', 'games_completed', 'games_ended_early', 'games_abandoned',
        'unique_hosts', 'total_player_sessions',
        'avg_duration_s', 'replay_rate', 'completion_rate',
    ];

    protected $casts = [
        'date' => 'date',
        'computed_at' => 'datetime',
        'replay_rate' => 'float',
        'completion_rate' => 'float',
    ];
}
