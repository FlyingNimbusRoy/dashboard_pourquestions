<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureToggle extends Model
{
    protected $table = 'feature_toggles';

    protected $fillable = [
        'type',
        'key',
        'enabled',
        'reason',
        'issue_id',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }
}
