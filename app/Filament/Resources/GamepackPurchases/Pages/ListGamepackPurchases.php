<?php

namespace App\Filament\Resources\GamepackPurchases\Pages;

use App\Filament\Resources\GamepackPurchases\GamepackPurchaseResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListGamepackPurchases extends ListRecords
{
    protected static string $resource = GamepackPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Grant access')];
    }
}
