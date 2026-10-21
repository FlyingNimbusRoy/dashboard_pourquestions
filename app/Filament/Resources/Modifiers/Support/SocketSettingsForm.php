<?php

namespace App\Filament\Resources\Modifiers\Support;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;

/** Typed fields for a socket modifier (dash_modifier_settings). $prefix is the state path, e.g. 'socket.'. */
class SocketSettingsForm
{
    /** @return list<Component> */
    public static function fields(string $prefix = ''): array
    {
        return [
            Toggle::make($prefix.'is_active')->label('Active in socket games')->inline(false)->default(true),
            Select::make($prefix.'gamepack_id')->label('Locked behind gamepack')
                ->options(fn () => \App\Models\Gamepack::query()->orderBy('name')->pluck('name', 'id')->all())
                ->searchable()->placeholder('Free (no pack needed)')
                ->helperText('Empty = free. If empty, the coupled gamepack of the game modifier applies.'),
            TextInput::make($prefix.'settings.duration')->label('Duration (turns)')->numeric()->integer()->minValue(1)->maxValue(20),
            TextInput::make($prefix.'settings.shot_multiplier')->label('Shot multiplier')->numeric()->minValue(1)->maxValue(10)
                ->helperText('Only where the modifier changes shots (High Tolerance, Bullied, Splitting Headache).'),
            TextInput::make($prefix.'settings.extra_shots')->label('Extra shots')->numeric()->integer()->minValue(0)->maxValue(10),
        ];
    }
}
