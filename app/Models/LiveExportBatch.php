<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One downloaded SQL export, kept so it can be downloaded again. */
#[Table('dash_live_export_batches')]
#[Fillable(['user_id', 'change_count', 'filename', 'sql', 'exported_at'])]
class LiveExportBatch extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['exported_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
