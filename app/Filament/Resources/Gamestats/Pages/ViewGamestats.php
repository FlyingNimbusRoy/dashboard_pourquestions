<?php

namespace App\Filament\Resources\Gamestats\Pages;

use App\Filament\Resources\Gamestats\GamestatsResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;

class ViewGamestats extends ViewRecord
{
    protected static string $resource = GamestatsResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
