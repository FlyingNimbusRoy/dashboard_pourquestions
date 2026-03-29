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
        'category',
        'flavor_text',
        'description',
        'icon',
        'color',
        'price',
        'url_coverart',
    ];

    const CATEGORIES = [
        'gamepack'     => 'Game Pack',
        'questionpack' => 'Question Pack',
        'skinpack'     => 'Skin Pack',
    ];

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function modifiers()
    {
        return $this->hasMany(Modifier::class, 'coupled_gamepack_id');
    }
}
