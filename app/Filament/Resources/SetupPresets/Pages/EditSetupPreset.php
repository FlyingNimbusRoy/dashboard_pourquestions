<?php

namespace App\Filament\Resources\SetupPresets\Pages;

use App\Filament\Resources\SetupPresets\SetupPresetResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use App\Services\Crud\SetupPresetService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditSetupPreset extends EditRecord
{
    protected static string $resource = SetupPresetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(fn (Model $record) => SetupPresetResource::deleteViaService($record)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(SetupPresetService::class)->update($record, $data);
        } catch (ValidationException $e) {
            Notification::make()->danger()->title(collect($e->errors())->flatten()->implode(' '))->send();
            $this->halt();
        }
    }
}
