<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashGameQuestionResult extends Model
{
    public $timestamps = false;

    protected $table = 'dash_game_question_results';

    protected $fillable = [
        'game_session_id', 'question_id', 'question_version', 'category_id',
        'round', 'correct_count', 'wrong_count', 'timeout_count',
        'option_counts',
    ];

    protected $casts = [
        'option_counts' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(DashGameSession::class, 'game_session_id');
    }
}
