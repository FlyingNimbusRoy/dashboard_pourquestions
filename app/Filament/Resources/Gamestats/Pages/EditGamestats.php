<?php

namespace App\Filament\Resources\Gamestats\Pages;

use App\Filament\Resources\Gamestats\GamestatsResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use App\Services\Crud\UserGamestatsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditGamestats extends EditRecord
{
    protected static string $resource = GamestatsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(UserGamestatsService::class)->update($record, $data);
        } catch (ValidationException $e) {
            Notification::make()->danger()->title(collect($e->errors())->flatten()->implode(' '))->send();
            $this->halt();
        }
    }
}
