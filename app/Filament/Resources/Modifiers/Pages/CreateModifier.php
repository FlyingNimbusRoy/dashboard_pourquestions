<?php

namespace App\Filament\Resources\Modifiers\Pages;

use App\Filament\Resources\Modifiers\ModifierResource;
use App\Services\Crud\ModifierService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateModifier extends CreateRecord
{
    protected static string $resource = ModifierResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(ModifierService::class)->create($data);
    }
}
