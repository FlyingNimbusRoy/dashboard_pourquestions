<?php

namespace App\Filament\Resources\Comments\Pages;

use App\Filament\Resources\Comments\CommentResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Services\Crud\CommentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateComment extends CreateRecord
{
    protected static string $resource = CommentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(CommentService::class)->create($data);
        } catch (ValidationException $e) {
            Notification::make()->danger()->title(collect($e->errors())->flatten()->implode(' '))->send();
            $this->halt();
        }
    }
}
