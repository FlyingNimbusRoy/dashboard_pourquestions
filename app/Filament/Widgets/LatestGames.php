<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Games\GameResource;
use App\Models\Game;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestGames extends TableWidget
{
    protected static ?int $sort = 9;

    protected static ?string $heading = 'Latest games';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Game::query()->latest('id')->limit(10))
            ->columns([
                TextColumn::make('created_at')->label('Started')->since(),
                TextColumn::make('name'),
                TextColumn::make('qcount')->label('Questions')
                    ->state(fn (Game $r) => count($r->question_array ?? [])),
            ])
            ->recordUrl(fn (Game $r) => GameResource::getUrl('view', ['record' => $r]))
            ->paginated(false)
            ->emptyStateHeading('No games yet');
    }
}
