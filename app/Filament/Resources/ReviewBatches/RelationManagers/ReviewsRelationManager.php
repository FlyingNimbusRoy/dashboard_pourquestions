<?php

namespace App\Filament\Resources\ReviewBatches\RelationManagers;

use App\Filament\Resources\Questions\QuestionResource;
use App\Models\QuestionReview;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $title = 'Questions';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['question', 'reviewer']))
            ->defaultSort('id')
            ->columns([
                TextColumn::make('question.vraag')
                    ->label('Question')
                    ->wrap()
                    ->url(fn (QuestionReview $record) => QuestionResource::getUrl('edit', ['record' => $record->question_id])),
                TextColumn::make('outcome')
                    ->badge(),
                TextColumn::make('difficulty')
                    ->state(fn (QuestionReview $record) => $record->difficulty_rating === null || $record->difficulty_rating === $record->difficulty_before
                        ? (string) $record->difficulty_before
                        : $record->difficulty_before.' → '.$record->difficulty_rating),
                IconColumn::make('answers_correct')
                    ->label('Answers')
                    ->boolean(),
                IconColumn::make('wording_clear')
                    ->label('Wording')
                    ->boolean(),
                IconColumn::make('category_correct')
                    ->label('Category')
                    ->boolean(),
                TextColumn::make('notes')
                    ->wrap()
                    ->limit(80),
                TextColumn::make('reviewer.name')
                    ->label('By'),
            ]);
    }
}
