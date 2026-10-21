<?php

namespace App\Filament\Resources\ReviewBatches\Pages;

use App\Filament\Pages\Review;
use App\Filament\Resources\ReviewBatches\ReviewBatchResource;
use Filament\Resources\Pages\ListRecords;

/** Legacy URL: batches are listed on the Review page. */
class ListReviewBatches extends ListRecords
{
    protected static string $resource = ReviewBatchResource::class;

    public function mount(): void
    {
        $this->redirect(Review::getUrl());
    }
}
