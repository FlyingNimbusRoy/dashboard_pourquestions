<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class LiveView extends Page
{
    protected string $view = 'filament.pages.live-view';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static string|UnitEnum|null $navigationGroup = 'Work queue';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Live view';

    protected static ?string $title = 'Live view';
}
