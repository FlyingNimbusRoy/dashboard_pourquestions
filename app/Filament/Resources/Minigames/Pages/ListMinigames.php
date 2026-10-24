<?php

namespace App\Filament\Resources\Minigames\Pages;

use App\Filament\Resources\Minigames\MinigameResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListMinigames extends ListRecords
{
    protected static string $resource = MinigameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('syncRegistry')
                ->label('Sync from registry')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Sync socket registry')
                ->modalDescription('Inserts any new minigames from database/data/socket-registry.json and fills in missing descriptions/settings. Existing rows are not overwritten.')
                ->action(function () {
                    Artisan::call('dashboard:sync-socket-registry');
                    $output = trim(Artisan::output());
                    Notification::make()
                        ->success()
                        ->title('Registry synced')
                        ->body($output ?: 'Done.')
                        ->persistent()
                        ->send();
                }),
        ];
    }
}
