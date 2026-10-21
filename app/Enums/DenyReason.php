<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DenyReason: string implements HasLabel
{
    case WrongAnswer = 'wrong_answer';
    case UnclearWording = 'unclear_wording';
    case WrongCategory = 'wrong_category';
    case Outdated = 'outdated';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::WrongAnswer => 'Wrong answer',
            self::UnclearWording => 'Unclear wording',
            self::WrongCategory => 'Wrong category',
            self::Outdated => 'Outdated',
            self::Other => 'Other',
        };
    }
}
