<?php

namespace App\Filament\Resources\Achievements\Pages;

use App\Filament\Resources\Achievements\AchievementResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Services\Crud\AchievementService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateAchievement extends CreateRecord
{
    protected static string $resource = AchievementResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(AchievementService::class)->create($data);
        } catch (ValidationException $e) {
            Notification::make()->danger()->title(collect($e->errors())->flatten()->implode(' '))->send();
            $this->halt();
        }
    }
}
