<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Questions\Support\ServiceDelete;
use App\Services\Crud\CategoryService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(ServiceDelete::single(CategoryService::class)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(CategoryService::class)->update($record, $data);
    }
}
