<?php

namespace App\Filament\Resources\Games\Pages;

use App\Filament\Resources\Games\GameResource;
use App\Models\Game;
use App\Services\Crud\GameService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewGame extends ViewRecord
{
    protected static string $resource = GameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancel')
                ->label('Cancel game')
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('Mark this game as cancelled. This cannot be undone.')
                ->visible(fn (Game $record) => ! $record->is_cancelled && ! $record->is_finished)
                ->action(function (Game $record) {
                    app(GameService::class)->markCancelled([$record], auth()->id());
                    Notification::make()->success()->title('Game cancelled.')->send();
                }),
            Action::make('finish')
                ->label('Mark as finished')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('Mark this game as finished. This cannot be undone.')
                ->visible(fn (Game $record) => ! $record->is_finished && ! $record->is_cancelled)
                ->action(function (Game $record) {
                    app(GameService::class)->markFinished([$record], auth()->id());
                    Notification::make()->success()->title('Game marked as finished.')->send();
                }),
        ];
    }
}
