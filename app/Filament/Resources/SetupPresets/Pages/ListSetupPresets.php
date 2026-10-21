<?php

namespace App\Filament\Resources\SetupPresets\Pages;

use App\Filament\Resources\SetupPresets\SetupPresetResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListSetupPresets extends ListRecords
{
    protected static string $resource = SetupPresetResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
