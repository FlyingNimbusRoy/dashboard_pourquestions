<?php

namespace App\Filament\Resources\GamePresets\Pages;

use App\Filament\Resources\GamePresets\GamePresetResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListGamePresets extends ListRecords
{
    protected static string $resource = GamePresetResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
