<?php

namespace App\Filament\Resources\Achievements\Pages;

use App\Filament\Resources\Achievements\AchievementResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use App\Services\Crud\AchievementService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditAchievement extends EditRecord
{
    protected static string $resource = AchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(fn (Model $record) => AchievementResource::deleteViaService($record)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(AchievementService::class)->update($record, $data);
        } catch (ValidationException $e) {
            Notification::make()->danger()->title(collect($e->errors())->flatten()->implode(' '))->send();
            $this->halt();
        }
    }
}
