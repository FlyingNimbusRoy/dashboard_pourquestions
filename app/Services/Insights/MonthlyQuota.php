<?php

namespace App\Services\Insights;

use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MonthlyQuota
{
    /**
     * @return array{
     *   total: int,
     *   target: int,
     *   remaining: int,
     *   pct_complete: int,
     *   pct_expected: int,
     *   days_left: int,
     *   days_total: int,
     *   day_of_month: int,
     *   expected: int,
     *   needed_per_day: int,
     *   pace: string,
     *   contributors: \Illuminate\Support\Collection,
     *   month_label: string,
     * }
     */
    public function get(): array
    {
        $target = config('dashboard.quota.monthly_target');
        $now = Carbon::now();
        $start = $now->copy()->startOfMonth();

        $daysTotal = $now->copy()->endOfMonth()->day;
        $daysDone = $now->day;
        $daysLeft = $daysTotal - $daysDone;

        $total = Question::where('created_at', '>=', $start)->count();
        $expected = (int) round($target * $daysDone / $daysTotal);
        $remaining = max(0, $target - $total);
        $neededPerDay = $daysLeft > 0 ? (int) ceil($remaining / $daysLeft) : 0;

        $streaks = $this->streaks($start, $now);

        $contributors = User::where('is_admin', true)
            ->withCount([
                'madeQuestions as month_count' => fn ($q) => $q->where('created_at', '>=', $start),
                'madeQuestions as total_count',
            ])
            ->orderByDesc('month_count')
            ->get()
            ->filter(fn (User $u) => $u->month_count > 0 || $u->total_count > 0)
            ->each(function (User $u) use ($streaks): void {
                $u->current_streak = $streaks[$u->id]['current'] ?? 0;
                $u->best_streak    = $streaks[$u->id]['best'] ?? 0;
            });

        $tolerance = max(1, (int) round($target * 0.05));
        $pace = match (true) {
            $total >= $target => 'achieved',
            $total > $expected => 'ahead',
            ($expected - $total) <= $tolerance => 'on_track',
            ($expected - $total) <= $tolerance * 3 => 'slightly_behind',
            default => 'behind',
        };

        return [
            'total'         => $total,
            'target'        => $target,
            'remaining'     => $remaining,
            'pct_complete'  => $target > 0 ? min(100, (int) round($total / $target * 100)) : 0,
            'pct_expected'  => $target > 0 ? min(100, (int) round($expected / $target * 100)) : 0,
            'days_left'     => $daysLeft,
            'days_total'    => $daysTotal,
            'day_of_month'  => $daysDone,
            'expected'      => $expected,
            'needed_per_day' => $neededPerDay,
            'pace'          => $pace,
            'contributors'  => $contributors,
            'month_label'   => $now->format('F Y'),
        ];
    }

    /**
     * Per-user streak data for the current month.
     * @return array<int, array{current:int, best:int}>
     */
    private function streaks(Carbon $start, Carbon $now): array
    {
        $rows = Question::where('created_at', '>=', $start)
            ->whereNotNull('maker_id')
            ->selectRaw('maker_id, DATE(created_at) as day')
            ->distinct()
            ->get();

        /** @var Collection<int, Collection<int, string>> $byUser */
        $byUser = $rows->groupBy('maker_id')->map(fn ($g) => $g->pluck('day')->sort()->values());

        $result = [];
        foreach ($byUser as $userId => $days) {
            $daysSet = array_flip($days->all());
            $today   = $now->toDateString();

            // Current streak: walk backward from today
            $current = 0;
            $check   = $now->copy();
            while (isset($daysSet[$check->toDateString()])) {
                $current++;
                $check->subDay();
            }

            // Best streak this month: longest consecutive run in the sorted list
            $best = 0;
            $run  = 0;
            $prev = null;
            foreach ($days as $day) {
                if ($prev !== null && Carbon::parse($day)->diffInDays(Carbon::parse($prev)) === 1) {
                    $run++;
                } else {
                    $run = 1;
                }
                $best = max($best, $run);
                $prev = $day;
            }

            $result[(int) $userId] = ['current' => $current, 'best' => $best];
        }

        return $result;
    }
}
