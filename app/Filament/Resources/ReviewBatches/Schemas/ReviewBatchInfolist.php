<?php

namespace App\Filament\Resources\ReviewBatches\Schemas;

use App\Models\ReviewBatch;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReviewBatchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('reviewer.name')
                            ->label('Reviewer')
                            ->placeholder('Anyone'),
                        TextEntry::make('progress')
                            ->state(fn (ReviewBatch $record) => ($record->reviews_count - $record->pending_count).' of '.$record->reviews_count.' reviewed'),
                        TextEntry::make('due_at')
                            ->label('Due')
                            ->date()
                            ->color(fn (ReviewBatch $record) => $record->isOverdue() ? 'danger' : null),
                        TextEntry::make('completed_at')
                            ->label('Completed')
                            ->dateTime()
                            ->placeholder('Open'),
                    ]),
            ]);
    }
}
