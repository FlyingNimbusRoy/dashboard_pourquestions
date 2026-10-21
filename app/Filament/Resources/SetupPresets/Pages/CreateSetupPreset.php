<?php

namespace App\Filament\Resources\SetupPresets\Pages;

use App\Filament\Resources\SetupPresets\SetupPresetResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Services\Crud\SetupPresetService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateSetupPreset extends CreateRecord
{
    protected static string $resource = SetupPresetResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(SetupPresetService::class)->create($data);
        } catch (ValidationException $e) {
            Notification::make()->danger()->title(collect($e->errors())->flatten()->implode(' '))->send();
            $this->halt();
        }
    }
}
