<?php

namespace App\Filament\Resources\Gamepacks\RelationManagers;

use App\Models\Minigame;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
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
                Action::make('link')
                    ->label('Link minigame')
                    ->icon(Heroicon::OutlinedPlus)
                    ->schema([
                        Select::make('minigame_ids')
                            ->label('Minigames')
                            ->options(fn () => Minigame::whereNull('gamepack_id')->orderBy('sort')->pluck('name', 'id'))
                            ->multiple()
                            ->required(),
                    ])
                    ->action(function (array $data, self $livewire) {
                        Minigame::whereIn('id', $data['minigame_ids'])
                            ->update(['gamepack_id' => $livewire->getOwnerRecord()->id, 'is_active' => true]);
                    }),
            ])
            ->recordActions([
                Action::make('unlink')
                    ->label('Unlink')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Minigame $record) {
                        $record->update(['gamepack_id' => null, 'is_active' => false]);
                    }),
            ])
            ->emptyStateHeading('No minigames linked')
            ->emptyStateDescription('Use "Link minigame" to attach minigames that unlock when this pack is owned.');
    }
}
