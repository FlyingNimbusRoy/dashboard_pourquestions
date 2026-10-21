<?php

namespace App\Filament\Resources\Comments\Pages;

use App\Filament\Resources\Comments\CommentResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use App\Services\Crud\CommentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditComment extends EditRecord
{
    protected static string $resource = CommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(fn (Model $record) => CommentResource::deleteViaService($record)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(CommentService::class)->update($record, $data);
        } catch (ValidationException $e) {
            Notification::make()->danger()->title(collect($e->errors())->flatten()->implode(' '))->send();
            $this->halt();
        }
    }
}
