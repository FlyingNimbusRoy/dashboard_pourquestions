<?php

namespace App\Filament\Resources\GamePresets\Pages;

use App\Filament\Resources\GamePresets\GamePresetResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Services\Crud\GamePresetService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateGamePreset extends CreateRecord
{
    protected static string $resource = GamePresetResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(GamePresetService::class)->create($data);
        } catch (ValidationException $e) {
            Notification::make()->danger()->title(collect($e->errors())->flatten()->implode(' '))->send();
            $this->halt();
        }
    }
}
