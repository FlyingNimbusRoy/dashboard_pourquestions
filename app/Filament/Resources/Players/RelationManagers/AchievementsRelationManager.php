<?php

namespace App\Filament\Resources\Players\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Read-only: changes go through the owning resource's service. */
class AchievementsRelationManager extends RelationManager
{
    protected static string $relationship = 'achievements';

    protected static ?string $title = 'Achievements';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('criteria_type')->badge(),
                TextColumn::make('pivot.unlocked_at')->label('Unlocked at')->dateTime()->placeholder('-'),
            ]);
    }
}
