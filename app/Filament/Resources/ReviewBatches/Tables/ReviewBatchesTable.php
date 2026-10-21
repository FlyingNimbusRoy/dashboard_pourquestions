<?php

namespace App\Filament\Resources\ReviewBatches\Tables;

use App\Filament\Resources\ReviewBatches\ReviewBatchResource;
use App\Models\ReviewBatch;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(25)
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('reviewer.name')
                    ->label('Reviewer')
                    ->placeholder('Anyone'),
                TextColumn::make('progress')
                    ->state(fn (ReviewBatch $record) => ($record->reviews_count - $record->pending_count).' / '.$record->reviews_count),
                TextColumn::make('due_at')
                    ->label('Due')
                    ->date()
                    ->sortable()
                    ->color(fn (ReviewBatch $record) => $record->isOverdue() ? 'danger' : null),
                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->since()
                    ->placeholder('Open')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('open')
                    ->label('Status')
                    ->placeholder('All')
                    ->trueLabel('Open')
                    ->falseLabel('Completed')
                    ->default(true)
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('completed_at'),
                        false: fn (Builder $query) => $query->whereNotNull('completed_at'),
                    ),
                Filter::make('mine')
                    ->label('Assigned to me')
                    ->query(fn (Builder $query) => $query->where('reviewer_id', auth()->id())),
            ])
            ->recordActions([
                Action::make('review')
                    ->label('Review')
                    ->icon(Heroicon::OutlinedPlay)
                    ->button()
                    ->url(fn (ReviewBatch $record) => ReviewBatchResource::getUrl('review', ['record' => $record]))
                    ->visible(fn (ReviewBatch $record) => $record->pending_count > 0),
                ViewAction::make(),
                Action::make('reassign')
                    ->icon(Heroicon::OutlinedUserCircle)
                    ->color('gray')
                    ->fillForm(fn (ReviewBatch $record) => ['reviewer_id' => $record->reviewer_id, 'due_at' => $record->due_at])
                    ->schema([
                        Select::make('reviewer_id')
                            ->label('Reviewer')
                            ->options(fn () => User::reviewers()->orderBy('name')->pluck('name', 'id'))
                            ->placeholder('Anyone'),
                        DatePicker::make('due_at')
                            ->label('Due'),
                    ])
                    ->action(fn (ReviewBatch $record, array $data) => $record->update($data))
                    ->visible(fn (ReviewBatch $record) => $record->completed_at === null),
                DeleteAction::make()
                    ->modalDescription('Pending questions return to the pool; completed reviews are deleted with the batch, the questions keep their validation date.'),
            ]);
    }
}
