<?php

namespace App\Filament\Widgets;

use App\Models\Gamepack;
use App\Services\Insights\GamepackOwners;
use App\Services\Insights\QuestionUsage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

/** Performance header on the gamepack edit page. */
class GamepackPerformance extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    public ?Model $record = null;

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        /** @var Gamepack $pack */
        $pack = $this->record;
        $owners = collect(app(GamepackOwners::class)->perPack())->firstWhere('id', $pack->id) ?? ['owners' => 0, 'grants' => 0];
        $u = app(QuestionUsage::class)->forGamepack($pack->id) ?? ['questions' => 0, 'asked' => 0, 'asks' => 0, 'pct' => 0, 'avg' => 0];

        return [
            Stat::make('Questions', number_format($u['questions'])),
            Stat::make('Asked at least once', $u['pct'].'%')->description("{$u['asked']} of {$u['questions']} questions"),
            Stat::make('Total asks', number_format($u['asks']))->description("{$u['avg']} per question"),
            Stat::make('Owners', number_format($owners['owners']))->description($owners['grants'].' via admin grant'),
            Stat::make('Purchases', number_format($pack->purchases()->count())),
            Stat::make('Characters / modifiers', $pack->characters()->count().' / '.$pack->modifiers()->count()),
        ];
    }
}
