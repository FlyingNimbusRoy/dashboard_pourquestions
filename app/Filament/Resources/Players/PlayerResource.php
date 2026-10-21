<?php

namespace App\Filament\Resources\Players;

use App\Filament\Resources\Players\Pages\EditPlayer;
use App\Filament\Resources\Players\Pages\ListPlayers;
use App\Filament\Resources\Players\Pages\ViewPlayer;
use App\Filament\Resources\Players\RelationManagers\AchievementsRelationManager;
use App\Filament\Resources\Players\RelationManagers\GamepackPurchasesRelationManager;
use App\Filament\Resources\Players\RelationManagers\HostedGamesRelationManager;
use App\Models\User;
use App\Services\Crud\PlayerService;
use App\Services\Crud\UserService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use UnitEnum;

class PlayerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Game setup';

    protected static ?int $navigationSort = 29;

    protected static ?string $modelLabel = 'player';

    protected static ?string $pluralModelLabel = 'players';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return app(PlayerService::class)->query()->withCount('hostedGames');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->required()->maxLength(255),
            Toggle::make('is_admin')->label('Dashboard admin'),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(3)->columnSpanFull()->schema([
                TextEntry::make('name'),
                IconEntry::make('is_admin')->label('Admin')->boolean(),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('hosted_games_count')->label('Games hosted'),
                TextEntry::make('achievements_count')->label('Achievements'),
                TextEntry::make('gamestats.games_played')->label('Games played')->placeholder('-'),
                TextEntry::make('gamestats.games_won')->label('Games won')->placeholder('-'),
                TextEntry::make('gamestats.total_shots')->label('Total shots')->placeholder('-'),
                TextEntry::make('gamestats.shot_highscore')->label('Shot highscore')->placeholder('-'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(25)
            ->defaultSort('name')
            ->columns([
            TextColumn::make('name')->searchable()->sortable(),
            IconColumn::make('is_admin')->label('Admin')->boolean()->sortable(),
            TextColumn::make('hosted_games_count')->label('Games hosted')->sortable(),
            TextColumn::make('achievements_count')->label('Achievements')->sortable(),
            TextColumn::make('gamestats.games_played')->label('Played')->placeholder('-'),
            TextColumn::make('gamestats.games_won')->label('Won')->placeholder('-'),
            TextColumn::make('gamestats.total_shots')->label('Shots')->placeholder('-'),
            TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            TernaryFilter::make('is_admin')->label('Admin')
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ActionGroup::make([
                    Action::make('toggleAdmin')
                        ->label(fn (User $record) => $record->is_admin ? 'Revoke admin' : 'Make admin')
                        ->icon(fn (User $record) => $record->is_admin ? Heroicon::OutlinedShieldExclamation : Heroicon::OutlinedShieldCheck)
                        ->color(fn (User $record) => $record->is_admin ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->action(function (User $record) {
                            try {
                                app(UserService::class)->toggleAdmin($record, auth()->user());
                                $status = $record->fresh()->is_admin ? 'granted' : 'revoked';
                                Notification::make()->success()->title("Admin access {$status} for {$record->name}.")->send();
                            } catch (ValidationException $e) {
                                Notification::make()->danger()
                                    ->title('Not allowed')
                                    ->body(collect($e->errors())->flatten()->first())
                                    ->persistent()
                                    ->send();
                            }
                        }),
                    Action::make('assignAllGamepacks')
                        ->label('Assign all gamepacks')
                        ->icon(Heroicon::OutlinedGift)
                        ->requiresConfirmation()
                        ->modalDescription(fn (User $record) => "Assigns every gamepack {$record->name} does not yet own, tagged as admin-granted.")
                        ->action(function (User $record) {
                            $count = app(UserService::class)->assignAllGamepacks($record);
                            $message = $count > 0
                                ? "Assigned {$count} gamepack(s) to {$record->name}."
                                : "{$record->name} already owns all gamepacks.";
                            Notification::make()->success()->title($message)->send();
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AchievementsRelationManager::class,
            HostedGamesRelationManager::class,
            GamepackPurchasesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlayers::route('/'),
            'view' => ViewPlayer::route('/{record}'),
            'edit' => EditPlayer::route('/{record}/edit'),
        ];
    }
}
