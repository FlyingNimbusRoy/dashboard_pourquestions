<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashIssueActivity extends Model
{
    public $timestamps = false;

    protected $table = 'dash_issue_activity';

    protected $fillable = [
        'issue_id', 'user_id', 'action', 'from_value', 'to_value', 'note',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(DashIssue::class, 'issue_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
