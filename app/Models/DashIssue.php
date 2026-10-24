<?php

namespace App\Models;

use App\Observers\DashIssueObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(DashIssueObserver::class)]

class DashIssue extends Model
{
    protected $table = 'dash_issues';

    protected $fillable = [
        'type', 'subject_type', 'subject_id',
        'reason_code', 'reason_summary', 'metric_snapshot',
        'status', 'assignee_id', 'created_by', 'resolved_at',
    ];

    protected $casts = [
        'metric_snapshot' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function activity(): HasMany
    {
        return $this->hasMany(DashIssueActivity::class, 'issue_id')->latest('id');
    }

    /**
     * Create a new issue, or reopen a resolved one, for the given subject + reason.
     * Does nothing if an active (open/assigned/in_progress) issue already exists.
     */
    public static function raiseOrReopen(
        string $type,
        string $subjectType,
        ?int $subjectId,
        string $reasonCode,
        string $reasonSummary,
        array $metrics = [],
    ): void {
        $active = self::query()
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->where('reason_code', $reasonCode)
            ->whereIn('status', ['open', 'assigned', 'in_progress'])
            ->first();

        if ($active) {
            $active->update(['metric_snapshot' => $metrics ?: null]);
            return;
        }

        $resolved = self::query()
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->where('reason_code', $reasonCode)
            ->where('status', 'resolved')
            ->latest()
            ->first();

        if ($resolved) {
            $resolved->update([
                'status' => 'open',
                'resolved_at' => null,
                'metric_snapshot' => $metrics ?: null,
                'reason_summary' => $reasonSummary,
            ]);
            DashIssueActivity::create([
                'issue_id' => $resolved->id,
                'user_id' => null,
                'action' => 'reopened',
                'from_value' => 'resolved',
                'to_value' => 'open',
                'note' => 'Threshold breach detected again (regression).',
            ]);
            return;
        }

        self::create([
            'type' => $type,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'reason_code' => $reasonCode,
            'reason_summary' => $reasonSummary,
            'metric_snapshot' => $metrics ?: null,
            'status' => 'open',
            'created_by' => null,
        ]);
    }
}
