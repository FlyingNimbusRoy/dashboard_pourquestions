<?php

namespace App\Filament\Resources\Games\Pages;

use App\Filament\Resources\Games\GameResource;
use App\Filament\Widgets\GameActivityStats;
use App\Filament\Widgets\GamesPerMonthChart;
use App\Filament\Widgets\QuestionsPerGameChart;
use App\Services\Crud\GameService;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListGames extends ListRecords
{
    protected static string $resource = GameResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            GameActivityStats::class,
            GamesPerMonthChart::class,
            QuestionsPerGameChart::class,
        ];
    }

    public function getTabs(): array
    {
        $svc = app(GameService::class);
        $cutoff = $svc->staleCutoff();

        return [
            'all' => Tab::make('All'),
            'stale' => Tab::make('Stale')->badge($svc->staleCount())->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_finished', false)->where('is_cancelled', false)->where('created_at', '<', $cutoff)),
            'running' => Tab::make('Running')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_finished', false)->where('is_cancelled', false)->where('created_at', '>=', $cutoff)),
            'finished' => Tab::make('Finished')->modifyQueryUsing(fn (Builder $query) => $query->where('is_finished', true)),
            'cancelled' => Tab::make('Cancelled')->modifyQueryUsing(fn (Builder $query) => $query->where('is_cancelled', true)),
        ];
    }
}
