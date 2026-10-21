<?php

namespace App\Services\LiveSync;

use Illuminate\Database\Eloquent\Model;

/** Attach to a game-content model: every insert/update/delete is queued for export to the live database. */
trait RecordsLiveChanges
{
    public static function bootRecordsLiveChanges(): void
    {
        static::created(fn (Model $m) => LiveChangeRecorder::created($m));
        static::updated(fn (Model $m) => LiveChangeRecorder::updated($m));
        static::deleting(fn (Model $m) => LiveChangeRecorder::deleting($m));
    }
}
