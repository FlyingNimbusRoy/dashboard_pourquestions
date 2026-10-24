<?php

namespace App\Filament\Resources\WorkQueue\Pages;

use App\Filament\Resources\WorkQueue\SupportMessageResource;
use Filament\Resources\Pages\ListRecords;

class ListSupportMessages extends ListRecords
{
    protected static string $resource = SupportMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
