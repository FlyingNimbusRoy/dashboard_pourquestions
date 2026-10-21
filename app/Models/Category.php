<?php

namespace App\Models;

use App\Services\LiveSync\RecordsLiveChanges;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'icon', 'color'])]
class Category extends Model
{
    use RecordsLiveChanges;

    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /** @return HasMany<Question, $this> */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
