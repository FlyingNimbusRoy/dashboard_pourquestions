<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'gamepack_id',
        'color_primary',
        'color_secondary',
        'color_muted_primary',
        'color_muted_secondary',
        'show_on_homepage',
        'parent_character_id',
    ];

    public function gamepack()
    {
        return $this->belongsTo(Gamepack::class);
    }

    public function parent()
    {
        return $this->belongsTo(Character::class, 'parent_character_id');
    }
}
