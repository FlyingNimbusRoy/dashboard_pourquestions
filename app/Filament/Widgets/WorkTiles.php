<?php

namespace App\Filament\Widgets;

use App\Enums\ReviewOutcome;
use App\Enums\SimilarityStatus;
use App\Filament\Resources\Games\GameResource;
use App\Filament\Resources\NeedsFix\NeedsFixResource;
use App\Filament\Resources\QuestionSimilarities\QuestionSimilarityResource;
use App\Filament\Resources\ReviewBatches\ReviewBatchResource;
use App\Models\QuestionReview;
use App\Models\QuestionSimilarity;
use App\Models\ReviewBatch;
use App\Services\Crud\GameService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** "Today" work tiles: what needs doing first. */
class WorkTiles extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Your work';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $uid = auth()->id();
        $batches = ReviewBatch::open()->where('reviewer_id', $uid)->withCount([
            'reviews as total_count',
            'reviews as done_count' => fn ($q) => $q->where('outcome', '!=', 'pending'),
        ])->get();
        $total = (int) $batches->sum('total_count');
        $done = (int) $batches->sum('done_count');
        $needsFix = QuestionReview::where('outcome', ReviewOutcome::NeedsFix)->whereNull('resolved_at')->count();
        $open = QuestionSimilarity::where('status', SimilarityStatus::Open)->count();
        $stale = app(GameService::class)->staleCount();

        return [
            Stat::make('My review batch', $batches->isEmpty() ? 'None open' : "{$done} / {$total}")
                ->description($batches->isEmpty() ? 'No open batch assigned to you' : ($total - $done).' questions left')
                ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                ->url(ReviewBatchResource::getUrl()),
            Stat::make('Needs fix', number_format($needsFix))
                ->description('Flagged by reviewers, not yet fixed')
                ->descriptionColor($needsFix > 0 ? 'warning' : 'gray')
                ->icon(Heroicon::OutlinedFlag)
                ->url(NeedsFixResource::getUrl()),
            Stat::make('Open duplicates', number_format($open))
                ->description('Question pairs waiting for a decision')
                ->icon(Heroicon::OutlinedDocumentDuplicate)
                ->url(QuestionSimilarityResource::getUrl()),
            Stat::make('Stale games', number_format($stale))
                ->description('Unfinished for over '.config('dashboard.games.stale_after_hours').' hours')
                ->descriptionColor($stale > 0 ? 'warning' : 'gray')
                ->icon(Heroicon::OutlinedClock)
                ->url(GameResource::getUrl('index', ['activeTab' => 'stale'])),
        ];
    }
}
