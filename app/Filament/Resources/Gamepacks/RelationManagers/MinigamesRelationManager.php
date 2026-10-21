<?php

namespace App\Filament\Resources\Gamepacks\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MinigamesRelationManager extends RelationManager
{
    protected static string $relationship = 'minigames';

    protected static ?string $title = 'Minigames';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('sort')->label('#')->sortable(),
                TextColumn::make('key')->badge()->searchable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('min_players')->label('Min players')->placeholder('—'),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->emptyStateHeading('No minigames linked')
            ->emptyStateDescription('Set gamepack_id on a minigame row in dash_minigames to link it here.');
    }
}
