<?php

namespace App\Filament\Resources\Gamepacks\Pages;

use App\Filament\Resources\Gamepacks\GamepackResource;
use App\Filament\Resources\Questions\Support\ServiceDelete;
use App\Services\Crud\GamepackService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditGamepack extends EditRecord
{
    protected static string $resource = GamepackResource::class;

    protected function getHeaderWidgets(): array
    {
        return [\App\Filament\Widgets\GamepackPerformance::class];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(ServiceDelete::single(GamepackService::class)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(GamepackService::class)->update($record, $data);
    }
}
