<?php

namespace App\Filament\Resources\Comments\Pages;

use App\Filament\Resources\Comments\CommentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListComments extends ListRecords
{
    protected static string $resource = CommentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
