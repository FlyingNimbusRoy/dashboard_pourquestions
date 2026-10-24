<?php

namespace App\Filament\Resources\Modifiers\Pages;

use App\Filament\Resources\Modifiers\ModifierResource;
use App\Filament\Resources\Modifiers\Widgets\UnmatchedSocketModifiers;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListModifiers extends ListRecords
{
    protected static string $resource = ModifierResource::class;

    protected function getFooterWidgets(): array
    {
        return [UnmatchedSocketModifiers::class];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
