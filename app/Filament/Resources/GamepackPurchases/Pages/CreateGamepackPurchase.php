<?php

namespace App\Filament\Resources\GamepackPurchases\Pages;

use App\Filament\Resources\GamepackPurchases\GamepackPurchaseResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Services\Crud\GamepackPurchaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateGamepackPurchase extends CreateRecord
{
    protected static string $resource = GamepackPurchaseResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(GamepackPurchaseService::class)->create($data);
        } catch (ValidationException $e) {
            Notification::make()->danger()->title(collect($e->errors())->flatten()->implode(' '))->send();
            $this->halt();
        }
    }
}
