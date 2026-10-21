<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\QuestionResource;
use App\Services\Crud\QuestionService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateQuestion extends CreateRecord
{
    protected static string $resource = QuestionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(QuestionService::class)->create($data);
    }
}
