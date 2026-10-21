<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ReviewBatches\ReviewBatchResource;
use App\Models\ReviewBatch;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class MyOpenReviews extends TableWidget
{
    protected static ?int $sort = 20;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'My open reviews';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ReviewBatch::open()
                ->where(fn (Builder $query) => $query->where('reviewer_id', auth()->id())->orWhereNull('reviewer_id'))
                ->withCount([
                    'reviews as total_count',
                    'reviews as done_count' => fn (Builder $query) => $query->where('outcome', '!=', 'pending'),
                ])
                ->orderByRaw('due_at is null')
                ->orderBy('due_at')
                ->orderBy('id'))
            ->columns([
                TextColumn::make('name')->label('Batch'),
                TextColumn::make('progress')
                    ->label('Progress')
                    ->state(fn (ReviewBatch $record): string => "{$record->done_count} / {$record->total_count}"),
                TextColumn::make('due_at')
                    ->label('Due')
                    ->date()
                    ->placeholder('No due date')
                    ->color(fn (ReviewBatch $record): ?string => $record->isOverdue() ? 'danger' : null),
            ])
            ->recordActions([
                Action::make('review')
                    ->label('Review')
                    ->icon(Heroicon::OutlinedPlay)
                    ->url(fn (ReviewBatch $record): string => ReviewBatchResource::getUrl('review', ['record' => $record])),
            ])
            ->paginated(false)
            ->emptyStateHeading('No open reviews')
            ->emptyStateDescription('You have no open review batches.');
    }
}
