<?php

namespace App\Filament\Resources\Characters\Pages;

use App\Filament\Resources\Characters\CharacterResource;
use App\Services\Crud\CharacterService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCharacter extends CreateRecord
{
    protected static string $resource = CharacterResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CharacterService::class)->create($data);
    }
}
