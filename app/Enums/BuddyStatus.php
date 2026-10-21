<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum BuddyStatus: string implements HasLabel
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';

    public function getLabel(): string
    {
        return str($this->value)->replace('_', ' ')->ucfirst()->toString();
    }
}
