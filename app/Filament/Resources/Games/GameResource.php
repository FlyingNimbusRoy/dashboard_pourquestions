<?php

namespace App\Filament\Resources\Games;

use App\Filament\Resources\Games\Pages\ListGames;
use App\Filament\Resources\Games\Pages\ViewGame;
use App\Filament\Resources\Questions\QuestionResource;
use App\Models\Game;
use App\Services\Crud\GameService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class GameResource extends Resource
{
    protected static ?string $model = Game::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Game activity';

    protected static ?string $modelLabel = 'game';

    protected static ?string $pluralModelLabel = 'games';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return app(GameService::class)->query();
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(3)->columnSpanFull()->schema([
                TextEntry::make('name'),
                TextEntry::make('host.name')->label('Host')->placeholder('-'),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('players'),
                TextEntry::make('shotstaken')->label('Shots taken'),
                TextEntry::make('game_length')->label('Length'),
                IconEntry::make('is_nsfw')->label('NSFW')->boolean(),
                IconEntry::make('is_finished')->label('Finished')->boolean(),
                IconEntry::make('is_cancelled')->label('Cancelled')->boolean(),
            ]),
            Section::make('Questions')->columnSpanFull()->schema([
                TextEntry::make('question_array')->hiddenLabel()->placeholder('No questions')
                    ->state(fn (Game $record) => collect($record->question_array ?? [])
                        ->map(fn ($id) => is_scalar($id)
                            ? '<a class="text-primary-600 underline" href="'.e(QuestionResource::getUrl('edit', ['record' => $id])).'">#'.e($id).'</a>'
                            : e(json_encode($id)))
                        ->implode(' '))
                    ->html(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(25)
            ->emptyStateHeading('No games found')
            ->emptyStateDescription('Games appear here as soon as someone hosts one. Adjust the filters if you expected results.')
            ->emptyStateIcon(Heroicon::OutlinedPuzzlePiece)
            ->defaultSort('id', 'desc')
            ->columns([
            TextColumn::make('id')->sortable(),
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('host.name')->label('Host')->placeholder('-'),
            TextColumn::make('players')->sortable(),
            TextColumn::make('shotstaken')->label('Shots')->sortable(),
            IconColumn::make('is_finished')->label('Finished')->boolean(),
            IconColumn::make('is_cancelled')->label('Cancelled')->boolean(),
            IconColumn::make('is_nsfw')->label('NSFW')->boolean(),
            TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
            TernaryFilter::make('is_finished')->label('Finished'),
            TernaryFilter::make('is_cancelled')->label('Cancelled'),
            TernaryFilter::make('is_nsfw')->label('NSFW'),
            SelectFilter::make('host_id')->label('Host')->relationship('host', 'name')->searchable()->preload(),
            Filter::make('created')->label('Date range')
                ->schema([DatePicker::make('from'), DatePicker::make('until')])
                ->query(fn (Builder $query, array $data) => $query
                    ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                    ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d)))
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('cancel')
                    ->label('Cancel')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Mark this game as cancelled. This cannot be undone.')
                    ->visible(fn (Game $record) => ! $record->is_cancelled && ! $record->is_finished)
                    ->action(function (Game $record) {
                        app(GameService::class)->markCancelled([$record], auth()->id());
                        Notification::make()->success()->title('Game cancelled.')->send();
                    }),
                Action::make('finish')
                    ->label('Finish')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Mark this game as finished. This cannot be undone.')
                    ->visible(fn (Game $record) => ! $record->is_finished && ! $record->is_cancelled)
                    ->action(function (Game $record) {
                        app(GameService::class)->markFinished([$record], auth()->id());
                        Notification::make()->success()->title('Game marked as finished.')->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('cancel')
                        ->label('Cancel selected')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('Cancel all selected games. This cannot be undone.')
                        ->action(function (Collection $records) {
                            $n = app(GameService::class)->markCancelled($records, auth()->id());
                            Notification::make()->success()->title("{$n} game(s) cancelled.")->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('finish')
                        ->label('Mark selected as finished')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalDescription('Mark all selected games as finished. This cannot be undone.')
                        ->action(function (Collection $records) {
                            $n = app(GameService::class)->markFinished($records, auth()->id());
                            Notification::make()->success()->title("{$n} game(s) marked as finished.")->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGames::route('/'),
            'view' => ViewGame::route('/{record}'),
        ];
    }
}
