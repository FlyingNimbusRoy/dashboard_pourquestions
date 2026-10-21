<?php

namespace App\Filament\Resources\BoozeBuddies\Pages;

use App\Filament\Resources\BoozeBuddies\BoozeBuddyResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use App\Services\Crud\BoozeBuddyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditBoozeBuddy extends EditRecord
{
    protected static string $resource = BoozeBuddyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(fn (Model $record) => BoozeBuddyResource::deleteViaService($record)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(BoozeBuddyService::class)->update($record, $data);
        } catch (ValidationException $e) {
            Notification::make()->danger()->title(collect($e->errors())->flatten()->implode(' '))->send();
            $this->halt();
        }
    }
}
