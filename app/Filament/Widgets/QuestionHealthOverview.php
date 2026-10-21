<?php

namespace App\Filament\Widgets;

use App\Enums\ReviewOutcome;
use App\Enums\SimilarityStatus;
use App\Filament\Resources\NeedsFix\NeedsFixResource;
use App\Filament\Resources\QuestionSimilarities\QuestionSimilarityResource;
use App\Filament\Resources\Questions\QuestionResource;
use App\Filament\Resources\ReviewBatches\ReviewBatchResource;
use App\Models\Question;
use App\Models\QuestionReview;
use App\Models\QuestionSimilarity;
use App\Models\ReviewBatch;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QuestionHealthOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        $counts = Cache::remember('widget.question-health', 60, fn (): array => $this->counts());
        ['total' => $total, 'due' => $due, 'uncategorised' => $uncategorised, 'duplicates' => $duplicates, 'openSimilar' => $openSimilar, 'needsFix' => $needsFix] = $counts;
        $myBatches = Cache::remember('widget.question-health.batches.'.auth()->id(), 60, fn () => ReviewBatch::open()->where('reviewer_id', auth()->id())->count());
        $validatedShare = $total > 0 ? round(($total - $due) / $total * 100) : 0;

        return [
            Stat::make('Questions', number_format($total))
                ->description($uncategorised.' without category')
                ->icon(Heroicon::OutlinedQuestionMarkCircle)
                ->url(QuestionResource::getUrl()),
            Stat::make('Validated', $validatedShare.'%')
                ->description(number_format($due).' due for review (older than '.config('dashboard.validation.revalidate_after_days').' days)')
                ->icon(Heroicon::OutlinedShieldCheck)
                ->url(QuestionResource::getUrl('index', ['filters' => ['validation' => ['value' => 'due']]])),
            Stat::make('Similar pairs to review', number_format($openSimilar))
                ->description($duplicates.' likely duplicates')
                ->descriptionColor($duplicates > 0 ? 'danger' : 'gray')
                ->icon(Heroicon::OutlinedDocumentDuplicate)
                ->url(QuestionSimilarityResource::getUrl()),
            Stat::make('Needs fix', number_format($needsFix))
                ->description($myBatches === 1 ? 'You have 1 open review batch' : "You have {$myBatches} open review batches")
                ->icon(Heroicon::OutlinedFlag)
                ->url($needsFix > 0 ? NeedsFixResource::getUrl() : ReviewBatchResource::getUrl()),
        ];
    }

    /** @return array<string, int> */
    private function counts(): array
    {
        $total = Question::count();
        $due = Question::validationDue()->count();
        $uncategorised = Question::whereNull('category_id')->count();
        $duplicates = QuestionSimilarity::where('status', SimilarityStatus::Open)
            ->where('score', '>=', config('dashboard.similarity.duplicate_threshold'))->count();
        $openSimilar = QuestionSimilarity::where('status', SimilarityStatus::Open)->count();
        $needsFix = QuestionReview::where('outcome', ReviewOutcome::NeedsFix)->whereNull('resolved_at')->count();

        return compact('total', 'due', 'uncategorised', 'duplicates', 'openSimilar', 'needsFix');
    }
}
