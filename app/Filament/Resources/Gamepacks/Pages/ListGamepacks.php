<?php

namespace App\Filament\Resources\Gamepacks\Pages;

use App\Filament\Resources\Gamepacks\GamepackResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGamepacks extends ListRecords
{
    protected static string $resource = GamepackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
