<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $table = 'achievements';

    protected $fillable = [
        'name',
        'description',
        'image',
        'criteria_type',
        'criteria_value',
    ];

    const CRITERIA_TYPES = [
        'games_played'  => 'Games Played',
        'games_won'     => 'Games Won',
        'total_shots'   => 'Total Shots',
        'shot_highscore' => 'Shot Highscore',
    ];

    public function getCriteriaLabelAttribute(): string
    {
        return self::CRITERIA_TYPES[$this->criteria_type] ?? ucfirst($this->criteria_type);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }
}