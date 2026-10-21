<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('description')
                            ->maxLength(255),
                        TextInput::make('icon')
                            ->maxLength(255)
                            ->helperText('As used by the game (icon name or image path).'),
                        ColorPicker::make('color'),
                    ]),
            ]);
    }
}
