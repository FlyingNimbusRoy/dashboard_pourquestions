<?php

namespace App\Filament\Resources\Gamepacks\RelationManagers;

use App\Models\Minigame;
use Filament\Actions\AssociateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MinigamesRelationManager extends RelationManager
{
    protected static string $relationship = 'minigames';

    protected static ?string $title = 'Minigames';

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
            ->headerActions([
                AssociateAction::make()
                    ->recordSelectOptionsQuery(fn ($query) => $query->whereNull('gamepack_id'))
                    ->after(function (Minigame $record) {
                        $record->is_active = true;
                        $record->save();
                    }),
            ])
            ->recordActions([
                DissociateAction::make()
                    ->after(function (Minigame $record) {
                        $record->is_active = false;
                        $record->save();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No minigames linked')
            ->emptyStateDescription('Attach minigames from the global pool to make them available when this pack is owned.');
    }
}
