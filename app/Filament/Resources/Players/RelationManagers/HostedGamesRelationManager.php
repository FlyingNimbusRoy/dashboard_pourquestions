<?php

namespace App\Filament\Resources\Players\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Read-only: changes go through the owning resource's service. */
class HostedGamesRelationManager extends RelationManager
{
    protected static string $relationship = 'hostedGames';

    protected static ?string $title = 'Hosted games';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('name')->searchable(),
                TextColumn::make('players'),
                TextColumn::make('shotstaken')->label('Shots'),
                IconColumn::make('is_finished')->label('Finished')->boolean(),
                IconColumn::make('is_cancelled')->label('Cancelled')->boolean(),
                TextColumn::make('created_at')->dateTime(),
            ]);
    }
}
