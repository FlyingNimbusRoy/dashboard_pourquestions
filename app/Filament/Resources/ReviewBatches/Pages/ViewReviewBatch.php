<?php

namespace App\Filament\Resources\ReviewBatches\Pages;

use App\Filament\Resources\ReviewBatches\ReviewBatchResource;
use App\Models\ReviewBatch;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewReviewBatch extends ViewRecord
{
    protected static string $resource = ReviewBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('review')
                ->label('Start reviewing')
                ->icon(Heroicon::OutlinedPlay)
                ->url(fn (ReviewBatch $record) => ReviewBatchResource::getUrl('review', ['record' => $record]))
                ->visible(fn (ReviewBatch $record) => $record->completed_at === null),
        ];
    }
}
