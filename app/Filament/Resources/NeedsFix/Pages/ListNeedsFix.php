<?php

namespace App\Filament\Resources\NeedsFix\Pages;

use App\Filament\Pages\Review;
use App\Filament\Resources\NeedsFix\NeedsFixResource;
use Filament\Resources\Pages\ListRecords;

/** Legacy URL: the denied bin now lives in the Denied tab of the Review page. */
class ListNeedsFix extends ListRecords
{
    protected static string $resource = NeedsFixResource::class;

    public function mount(): void
    {
        $this->redirect(Review::getUrl(['tab' => 'denied']));
    }
}
