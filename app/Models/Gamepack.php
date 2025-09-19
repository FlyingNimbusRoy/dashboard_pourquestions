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
}
