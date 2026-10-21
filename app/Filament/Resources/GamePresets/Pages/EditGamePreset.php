<?php

namespace App\Filament\Resources\GamePresets\Pages;

use App\Filament\Resources\GamePresets\GamePresetResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use App\Services\Crud\GamePresetService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditGamePreset extends EditRecord
{
    protected static string $resource = GamePresetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(fn (Model $record) => GamePresetResource::deleteViaService($record)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(GamePresetService::class)->update($record, $data);
        } catch (ValidationException $e) {
            Notification::make()->danger()->title(collect($e->errors())->flatten()->implode(' '))->send();
            $this->halt();
        }
    }
}
