<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DashGameSession extends Model
{
    public $timestamps = false;

    protected $table = 'dash_game_sessions';

    protected $fillable = [
        'game_id', 'pin', 'host_user_id', 'host_device_id',
        'status', 'schema_version', 'app_version',
        'player_count', 'question_count',
        'started_at', 'ended_at', 'duration_s',
        'lead_changes', 'last_phase_type', 'last_phase_round',
        'settings', 'payload',
    ];

    protected $casts = [
        'settings' => 'array',
        'payload' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'ingested_at' => 'datetime',
    ];

    public function questionResults(): HasMany
    {
        return $this->hasMany(DashGameQuestionResult::class, 'game_session_id');
    }
}
