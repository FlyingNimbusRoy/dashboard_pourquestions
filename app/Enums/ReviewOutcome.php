<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ReviewOutcome: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Approved = 'approved';
    case NeedsFix = 'needs_fix';
    case Skipped = 'skipped';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::NeedsFix => 'Needs fix',
            self::Skipped => 'Skipped',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Approved => 'success',
            self::NeedsFix => 'danger',
            self::Skipped => 'warning',
        };
    }
}
