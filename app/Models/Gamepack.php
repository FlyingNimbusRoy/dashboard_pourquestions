<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gamepack extends Model
{
    use HasFactory;

    protected $table = 'gamepacks';

    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
        'price',
        'url_coverart',
    ];

    /**
     * Alle modifiers die gekoppeld zijn aan dit gamepack.
     */
    public function modifiers()
    {
        return $this->hasMany(Modifier::class, 'coupled_gamepack_id');
    }
}
