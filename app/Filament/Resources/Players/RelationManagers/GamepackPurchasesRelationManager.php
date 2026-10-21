<?php

namespace App\Filament\Resources\Players\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Read-only: changes go through the owning resource's service. */
class GamepackPurchasesRelationManager extends RelationManager
{
    protected static string $relationship = 'gamepackPurchases';

    protected static ?string $title = 'Gamepack purchases';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('gamepack.name')->label('Gamepack'),
                IconColumn::make('opened')->boolean(),
                IconColumn::make('access_through_admin_grant')->label('Admin grant')->boolean(),
                TextColumn::make('created_at')->dateTime(),
            ]);
    }
}
