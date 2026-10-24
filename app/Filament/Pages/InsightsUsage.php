<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Questions\QuestionResource;
use App\Models\Question;
use App\Services\Insights\QuestionUsage;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class InsightsUsage extends Page
{
    protected string $view = 'filament.pages.insights-usage';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Usage';

    /** @return array<string, mixed> */
    public function getViewData(): array
    {
        $usage = app(QuestionUsage::class);

        $top25Raw = $usage->top(25);
        $totalAsks = $usage->totalAsks();

        $top25Questions = Question::query()
            ->whereIn('id', array_column($top25Raw, 'id'))
            ->with('category')
            ->get(['id', 'vraag', 'category_id', 'difficulty'])
            ->keyBy('id');

        $top25 = array_map(fn ($row) => [
            'id'         => $row['id'],
            'asks'       => $row['asks'],
            'share'      => $totalAsks > 0 ? round($row['asks'] / $totalAsks * 100, 1) : 0.0,
            'vraag'      => $top25Questions[$row['id']]?->vraag ?? '(deleted)',
            'category'   => $top25Questions[$row['id']]?->category?->name ?? '—',
            'difficulty' => $top25Questions[$row['id']]?->difficulty ?? '—',
        ], $top25Raw);

        $histogram    = $usage->histogram();
        $histogramMax = max([1, ...array_values($histogram)]);

        $perCategory     = $usage->perCategory();
        $perGamepack     = $usage->perGamepack();
        $maxCategoryAsks = max([1, ...array_column($perCategory, 'asks')]);
        $maxGamepackAsks = max([1, ...array_column($perGamepack, 'asks')]);

        $totalQuestions  = $usage->totalQuestions();
        $neverAskedCount = count($usage->neverAskedIds());
        $distinctAsked   = $usage->distinctAsked();

        return [
            'top25'           => $top25,
            'totalQuestions'  => $totalQuestions,
            'distinctAsked'   => $distinctAsked,
            'neverAskedCount' => $neverAskedCount,
            'askedOnceCount'  => $histogram['1'] ?? 0,
            'totalAsks'       => $totalAsks,
            'orphanCount'     => $usage->orphanIds(),
            'pctAsked'        => $totalQuestions > 0 ? round($distinctAsked / $totalQuestions * 100) : 0,
            'histogram'       => $histogram,
            'histogramMax'    => $histogramMax,
            'perCategory'     => $perCategory,
            'perGamepack'     => $perGamepack,
            'maxCategoryAsks' => $maxCategoryAsks,
            'maxGamepackAsks' => $maxGamepackAsks,
            'neverAskedUrl' => QuestionResource::getUrl('index', ['activeTab' => 'never_asked']),
        ];
    }
}
