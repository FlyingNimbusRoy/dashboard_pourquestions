<?php

namespace App\Filament\Resources\Minigames\Pages;

use App\Filament\Resources\Minigames\MinigameResource;
use App\Services\Crud\MinigameService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMinigame extends CreateRecord
{
    protected static string $resource = MinigameResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(MinigameService::class)->create($data);
    }
}
