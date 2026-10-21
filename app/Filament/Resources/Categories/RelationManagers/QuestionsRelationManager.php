<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\Resources\Questions\QuestionResource;
use App\Filament\Resources\Questions\Tables\QuestionsTable;
use App\Models\Question;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

/** The category's questions, with the same filters and bulk "move" actions as the main list. */
class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    public function table(Table $table): Table
    {
        return QuestionsTable::configure($table, showCategory: false)
            ->recordActions([
                EditAction::make()->url(fn (Question $record) => QuestionResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
