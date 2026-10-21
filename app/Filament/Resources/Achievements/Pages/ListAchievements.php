<?php

namespace App\Filament\Resources\Achievements\Pages;

use App\Filament\Resources\Achievements\AchievementResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListAchievements extends ListRecords
{
    protected static string $resource = AchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
