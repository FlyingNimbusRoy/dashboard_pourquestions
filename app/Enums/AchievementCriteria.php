<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AchievementCriteria: string implements HasLabel
{
    case GamesPlayed = 'games_played';
    case GamesWon = 'games_won';
    case TotalShots = 'total_shots';
    case ShotHighscore = 'shot_highscore';
    case SingleGameShots = 'single_game_shots';
    case MemberNumber = 'member_number';

    public function getLabel(): string
    {
        return str($this->value)->replace('_', ' ')->ucfirst()->toString();
    }
}
