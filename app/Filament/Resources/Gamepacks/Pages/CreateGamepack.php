<?php

namespace App\Filament\Resources\Gamepacks\Pages;

use App\Filament\Resources\Gamepacks\GamepackResource;
use App\Services\Crud\GamepackService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateGamepack extends CreateRecord
{
    protected static string $resource = GamepackResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(GamepackService::class)->create($data);
    }
}
