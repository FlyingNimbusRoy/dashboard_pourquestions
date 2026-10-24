<?php

namespace App\Services\Analytics;

use App\Models\DashIssue;
use Illuminate\Support\Facades\DB;

class MinigameStats
{
    /**
     * Number of tracked game sessions that included this minigame key.
     * dash_game_sessions.payload.minigames is a JSON array of key strings.
     */
    public function appearanceCount(string $key): int
    {
        try {
            return (int) DB::table('dash_game_sessions')
                ->whereRaw("JSON_CONTAINS(JSON_EXTRACT(payload, '$.minigames'), JSON_QUOTE(?))", [$key])
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /** Number of open/active issues for this minigame (by dash_minigames id). */
    public function openIssueCount(int $minigameId): int
    {
        return DashIssue::where('subject_type', 'minigame')
            ->where('subject_id', $minigameId)
            ->whereIn('status', ['open', 'assigned', 'in_progress'])
            ->count();
    }
}
