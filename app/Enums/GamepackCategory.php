<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum GamepackCategory: string implements HasLabel
{
    case Gamepack = 'gamepack';
    case Questionpack = 'questionpack';
    case Skinpack = 'skinpack';

    public function getLabel(): string
    {
        return str($this->value)->replace('_', ' ')->ucfirst()->toString();
    }
}
