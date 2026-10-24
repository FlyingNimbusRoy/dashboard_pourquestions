<?php

namespace App\Filament\Resources\Operations\Pages;

use App\Filament\Resources\Operations\FeatureToggleResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditFeatureToggle extends EditRecord
{
    protected static string $resource = FeatureToggleResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['changed_by'] = auth()->id();

        $record->update($data);

        return $record;
    }
}
