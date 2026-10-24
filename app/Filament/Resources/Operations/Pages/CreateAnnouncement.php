<?php

namespace App\Filament\Resources\Operations\Pages;

use App\Filament\Resources\Operations\AnnouncementResource;
use App\Models\Announcement;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAnnouncement extends CreateRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['created_by'] = auth()->id();

        return Announcement::create($data);
    }
}
