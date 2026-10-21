<?php

namespace App\Filament\Resources\ReviewBatches\Widgets;

use App\Enums\ReviewOutcome;
use App\Models\QuestionReview;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

/** Small throughput row on the Review page. Zeros are fine: there may be no review activity yet. */
class ReviewThroughput extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected int|array|null $columns = 4;

    protected function getStats(): array
    {
        $week = QuestionReview::query()->where('reviewed_at', '>=', now()->startOfWeek());
        $approved = (clone $week)->where('outcome', ReviewOutcome::Approved)->count();
        $denied = (clone $week)->where('outcome', ReviewOutcome::NeedsFix)->count();
        $openDenied = QuestionReview::where('outcome', ReviewOutcome::NeedsFix)->whereNull('resolved_at')->count();
        $mine = QuestionReview::where('outcome', ReviewOutcome::Pending)
            ->whereHas('batch', fn (Builder $query) => $query->open()->where('reviewer_id', auth()->id()))
            ->count();

        $stats = [
            Stat::make('Reviewed this week', $approved + $denied)
                ->description("{$approved} approved, {$denied} denied")
                ->color($approved + $denied > 0 ? 'success' : 'gray'),
            Stat::make('Open denied', $openDenied)
                ->description($openDenied > 0 ? 'Waiting for a fix' : 'Nothing waiting')
                ->color($openDenied > 0 ? 'danger' : 'gray'),
            Stat::make('My open items', $mine)
                ->description($mine > 0 ? 'In your open batches' : 'Start a batch to get some')
                ->color($mine > 0 ? 'warning' : 'gray'),
        ];

        if (($avg = $this->averageSeconds()) !== null) {
            $stats[] = Stat::make('Avg. time per question', $avg >= 60 ? floor($avg / 60).'m '.($avg % 60).'s' : $avg.'s')
                ->description('From gaps between decisions, last 30 days')
                ->color('gray');
        }

        return $stats;
    }

    /** Average gap between consecutive decisions of one reviewer in one batch; gaps over 10 minutes (breaks) are ignored. */
    private function averageSeconds(): ?int
    {
        $gaps = [];
        QuestionReview::query()
            ->whereNotNull('reviewed_at')->where('reviewed_at', '>=', now()->subDays(30))
            ->whereIn('outcome', [ReviewOutcome::Approved, ReviewOutcome::NeedsFix])
            ->orderBy('reviewed_at')
            ->get(['id', 'review_batch_id', 'reviewer_id', 'reviewed_at'])
            ->groupBy(fn ($r) => $r->review_batch_id.'-'.$r->reviewer_id)
            ->each(function ($rows) use (&$gaps) {
                $rows->values()->each(function ($r, $i) use ($rows, &$gaps) {
                    if ($i > 0) {
                        $gap = $rows[$i - 1]->reviewed_at->diffInSeconds($r->reviewed_at, true);
                        if ($gap <= 600) {
                            $gaps[] = $gap;
                        }
                    }
                });
            });

        return $gaps === [] ? null : (int) round(array_sum($gaps) / count($gaps));
    }
}
