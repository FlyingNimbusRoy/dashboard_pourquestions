<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'vraag',
        'difficulty',
        'is_random',
        'is_nsfw',
        'trivia',
        'maker_id',
        'category_id',
        'gamepack_id',
    ];

    // Optional: if you want answers relationship
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function gamepack()
    {
        return $this->belongsTo(Gamepack::class);
    }

    public function maker()
    {
        return $this->belongsTo(User::class, 'maker_id');
    }
}
