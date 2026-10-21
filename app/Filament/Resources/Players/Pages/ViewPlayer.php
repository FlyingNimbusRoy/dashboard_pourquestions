<?php

namespace App\Filament\Resources\Players\Pages;

use App\Filament\Resources\Players\PlayerResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;

class ViewPlayer extends ViewRecord
{
    protected static string $resource = PlayerResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
