<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modifier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'turnbased',
        'effects',
        'coupled_gamepack_id',
        'is_active',
    ];

    protected $casts = [
        'turnbased' => 'boolean',
        'effects' => 'array',   // JSON automatisch als array
        'is_active' => 'boolean',
    ];

    /**
     * Koppeling naar het gamepack.
     */
    public function gamepack()
    {
        return $this->belongsTo(Gamepack::class, 'coupled_gamepack_id');
    }
}
