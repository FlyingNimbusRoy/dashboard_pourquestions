<?php

namespace App\Filament\Resources\Characters\Pages;

use App\Filament\Resources\Characters\CharacterResource;
use App\Filament\Resources\Questions\Support\ServiceDelete;
use App\Services\Crud\CharacterService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditCharacter extends EditRecord
{
    protected static string $resource = CharacterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(ServiceDelete::single(CharacterService::class)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(CharacterService::class)->update($record, $data);
    }
}
