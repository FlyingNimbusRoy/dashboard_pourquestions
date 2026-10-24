<?php

namespace App\Services\Analytics;

use App\Models\DashIssue;
use Illuminate\Support\Facades\DB;

class ModifierStats
{
    /**
     * Number of tracked game sessions where this modifier key was active.
     * dash_game_sessions.settings.modifiers is a JSON array of key strings.
     */
    public function appearanceCount(string $key): int
    {
        try {
            return (int) DB::table('dash_game_sessions')
                ->whereRaw("JSON_CONTAINS(JSON_EXTRACT(settings, '$.modifiers'), JSON_QUOTE(?))", [$key])
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /** Number of open/active issues for this modifier (by modifiers.id). */
    public function openIssueCount(int $modifierId): int
    {
        return DashIssue::where('subject_type', 'modifier')
            ->where('subject_id', $modifierId)
            ->whereIn('status', ['open', 'assigned', 'in_progress'])
            ->count();
    }
}
