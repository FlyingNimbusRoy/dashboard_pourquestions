<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SimilarityStatus: string implements HasColor, HasLabel
{
    case Open = 'open';
    case Dismissed = 'dismissed';
    case Resolved = 'resolved';

    public function getLabel(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Dismissed => 'Not a duplicate',
            self::Resolved => 'Resolved',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Open => 'warning',
            self::Dismissed => 'gray',
            self::Resolved => 'success',
        };
    }
}
