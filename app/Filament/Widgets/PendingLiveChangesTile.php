<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Insights\PendingLiveChanges;
use App\Models\PendingLiveChange;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Work tile: content changes made locally that are not yet exported to the live database. */
class PendingLiveChangesTile extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getColumns(): int
    {
        return 1;
    }

    protected function getStats(): array
    {
        $n = PendingLiveChange::open()->count();

        return [
            Stat::make('Changes waiting for live', number_format($n))
                ->description($n > 0 ? 'Download the SQL and apply it to live' : 'Local and live are in sync')
                ->descriptionColor($n > 0 ? 'warning' : 'success')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->url(PendingLiveChanges::getUrl()),
        ];
    }
}
