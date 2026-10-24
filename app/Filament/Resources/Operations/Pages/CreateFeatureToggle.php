<?php

namespace App\Filament\Resources\Operations\Pages;

use App\Filament\Resources\Operations\FeatureToggleResource;
use App\Models\FeatureToggle;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateFeatureToggle extends CreateRecord
{
    protected static string $resource = FeatureToggleResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['changed_by'] = auth()->id();

        return FeatureToggle::create($data);
    }
}
