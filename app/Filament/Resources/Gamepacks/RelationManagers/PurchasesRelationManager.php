<?php

namespace App\Filament\Resources\Gamepacks\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Read-only list of who bought this gamepack. */
class PurchasesRelationManager extends RelationManager
{
    protected static string $relationship = 'purchases';

    protected static ?string $title = 'Owners and purchases';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Purchased')->dateTime()->sortable(),
                TextColumn::make('user.name')->label('Player')->placeholder('-'),
                IconColumn::make('opened')->boolean(),
                IconColumn::make('access_through_admin_grant')->label('Admin grant')->boolean(),
            ])
            ->emptyStateHeading('No purchases yet');
    }
}
