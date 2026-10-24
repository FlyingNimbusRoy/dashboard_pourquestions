<?php

namespace App\Filament\Resources\Operations\Pages;

use App\Filament\Resources\Operations\FeatureToggleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeatureToggles extends ListRecords
{
    protected static string $resource = FeatureToggleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
