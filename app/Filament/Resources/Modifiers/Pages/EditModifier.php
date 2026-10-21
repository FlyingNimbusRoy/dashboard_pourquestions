<?php

namespace App\Filament\Resources\Modifiers\Pages;

use App\Filament\Resources\Modifiers\ModifierResource;
use App\Filament\Resources\Questions\Support\ServiceDelete;
use App\Services\Crud\ModifierService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditModifier extends EditRecord
{
    protected static string $resource = ModifierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(ServiceDelete::single(ModifierService::class)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(ModifierService::class)->update($record, $data);
    }
}
