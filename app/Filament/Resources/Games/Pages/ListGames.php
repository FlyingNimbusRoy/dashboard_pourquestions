<?php

namespace App\Filament\Resources\Games\Pages;

use App\Filament\Resources\Games\GameResource;
use App\Models\Game;
use App\Services\Crud\GameService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListGames extends ListRecords
{
    protected static string $resource = GameResource::class;

    private function service(): GameService
    {
        return app(GameService::class);
    }

    protected function getHeaderActions(): array
    {
        $hours = (int) config('dashboard.games.stale_after_hours');

        return [
            Action::make('finishStale')
                ->label(fn (): string => 'Finish all stale games ('.$this->service()->staleCount().')')
                ->icon('heroicon-o-check-circle')
                ->color('warning')
                ->visible(fn (): bool => $this->service()->staleCount() > 0)
                ->requiresConfirmation()
                ->modalHeading('Finish all stale games')
                ->modalDescription(fn (): string => 'This marks '.$this->service()->staleCount()." unfinished, uncancelled games older than {$hours} hours as finished on this local copy, and queues the same change as a pending live change (nothing is written to the live database).")
                ->action(function (): void {
                    $n = $this->service()->finishAllStale(auth()->id());
                    Notification::make()->title("{$n} games marked finished")->body('Queued under Insights > Pending live changes.')->success()->send();
                }),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return $this->service()->staleCount() > 0 ? 'stale' : 'running';
    }

    public function getTabs(): array
    {
        $svc = $this->service();
        $cutoff = $svc->staleCutoff();

        return [
            'stale' => Tab::make('Stale')->badge($svc->staleCount())->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_finished', false)->where('is_cancelled', false)->where('created_at', '<', $cutoff)),
            'running' => Tab::make('Running')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_finished', false)->where('is_cancelled', false)->where('created_at', '>=', $cutoff)),
            'finished' => Tab::make('Finished')->modifyQueryUsing(fn (Builder $query) => $query->where('is_finished', true)),
            'cancelled' => Tab::make('Cancelled')->modifyQueryUsing(fn (Builder $query) => $query->where('is_cancelled', true)),
            'all' => Tab::make('All'),
        ];
    }
}
