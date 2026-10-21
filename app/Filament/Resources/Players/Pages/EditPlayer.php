<?php

namespace App\Filament\Resources\Players\Pages;

use App\Filament\Resources\Players\PlayerResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use App\Services\Crud\PlayerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditPlayer extends EditRecord
{
    protected static string $resource = PlayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(PlayerService::class)->update($record, $data);
        } catch (ValidationException $e) {
            Notification::make()->danger()->title(collect($e->errors())->flatten()->implode(' '))->send();
            $this->halt();
        }
    }
}
