<?php

namespace App\Console\Commands\Analytics;

use App\Models\DashIssue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckThresholds extends Command
{
    protected $signature = 'analytics:check-thresholds
                            {--min-samples= : Minimum games before flagging a question (default from config)}';

    protected $description = 'Scan question-level game stats and raise or reopen dash_issues for threshold breaches.';

    public function handle(): int
    {
        $minSamples = (int) ($this->option('min-samples') ?? config('dashboard.analytics.min_samples', 10));
        $thresholds = config('dashboard.analytics.thresholds', []);

        $lowCorrect  = (float) ($thresholds['low_correct_rate']  ?? 0.20);
        $highCorrect = (float) ($thresholds['high_correct_rate'] ?? 0.95);
        $highTimeout = (float) ($thresholds['high_timeout_rate'] ?? 0.40);

        $this->line("Checking thresholds (min_samples={$minSamples})…");

        $rows = DB::table('dash_game_question_results')
            ->groupBy('question_id')
            ->selectRaw("
                question_id,
                COUNT(DISTINCT game_session_id) AS game_count,
                SUM(correct_count)              AS total_correct,
                SUM(wrong_count)                AS total_wrong,
                SUM(timeout_count)              AS total_timeout
            ")
            ->havingRaw('COUNT(DISTINCT game_session_id) >= ?', [$minSamples])
            ->get();

        $raised = 0;

        foreach ($rows as $row) {
            $total = $row->total_correct + $row->total_wrong + $row->total_timeout;
            if ($total === 0) continue;

            $correctRate = $row->total_correct / $total;
            $timeoutRate = $row->total_timeout / $total;
            $qid         = (int) $row->question_id;

            $metrics = [
                'game_count'   => (int) $row->game_count,
                'correct_rate' => round($correctRate, 4),
                'timeout_rate' => round($timeoutRate, 4),
            ];

            if ($correctRate < $lowCorrect) {
                DashIssue::raiseOrReopen('balance', 'question', $qid, 'low_correct_rate',
                    "Question #{$qid} correct rate " . round($correctRate * 100) . "% — below {$lowCorrect} threshold ({$row->game_count} games).",
                    $metrics);
                $raised++;
            }

            if ($correctRate > $highCorrect) {
                DashIssue::raiseOrReopen('balance', 'question', $qid, 'high_correct_rate',
                    "Question #{$qid} correct rate " . round($correctRate * 100) . "% — above {$highCorrect} threshold ({$row->game_count} games).",
                    $metrics);
                $raised++;
            }

            if ($timeoutRate > $highTimeout) {
                DashIssue::raiseOrReopen('content', 'question', $qid, 'high_timeout_rate',
                    "Question #{$qid} timeout rate " . round($timeoutRate * 100) . "% — above {$highTimeout} threshold ({$row->game_count} games).",
                    $metrics);
                $raised++;
            }

        }

        $this->info("Checked {$rows->count()} questions, raised/updated {$raised} issue(s).");
        return self::SUCCESS;
    }
}
