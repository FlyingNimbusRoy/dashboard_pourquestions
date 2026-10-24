<?php

namespace App\Filament\Resources\Minigames;

use App\Filament\Resources\Minigames\Pages\CreateMinigame;
use App\Filament\Resources\Minigames\Pages\EditMinigame;
use App\Filament\Resources\Minigames\Pages\ListMinigames;
use App\Filament\Resources\Questions\Support\ServiceDelete;
use App\Models\Minigame;
use App\Services\Crud\MinigameService;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MinigameResource extends Resource
{
    protected static ?string $model = Minigame::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static string|UnitEnum|null $navigationGroup = 'Game setup';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return app(MinigameService::class)->query();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('key')
                        ->required()
                        ->maxLength(64)
                        ->unique(ignoreRecord: true)
                        ->readOnly(fn (string $operation) => $operation === 'edit')
                        ->helperText('Must match the socket-server minigame key exactly. Cannot be changed after creation.'),
                    TextInput::make('name')->required()->maxLength(255),
                    Textarea::make('description')->rows(2)->columnSpanFull(),
                ]),
            Section::make('Availability')
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    Select::make('gamepack_id')
                        ->label('Locked behind gamepack')
                        ->relationship('gamepack', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('Free (no pack needed)')
                        ->helperText('Empty = available to all hosts. Set to require ownership of a pack.'),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->inline(false),
                    TextInput::make('sort')
                        ->label('Sort order')
                        ->numeric()
                        ->integer()
                        ->default(10)
                        ->minValue(0),
                ]),
            Section::make('Player limits')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('min_players')
                        ->label('Minimum players')
                        ->numeric()->integer()->minValue(1)->maxValue(8)
                        ->placeholder('None'),
                    TextInput::make('max_players')
                        ->label('Maximum players')
                        ->numeric()->integer()->minValue(1)->maxValue(8)
                        ->placeholder('None'),
                ]),
            Section::make('Selection settings')
                ->description('Controls how the socket server picks this minigame during a game.')
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    Toggle::make('settings.allowed_in_round_1')
                        ->label('Allowed in round 1')
                        ->default(true)
                        ->inline(false),
                    TextInput::make('settings.fixed_round')
                        ->label('Fixed round')
                        ->numeric()->integer()->minValue(1)->maxValue(10)
                        ->placeholder('Not fixed')
                        ->helperText('Force this minigame to a specific round (e.g. 2 for Cards of Misery).'),
                    TextInput::make('settings.weight')
                        ->label('Selection weight')
                        ->numeric()->integer()->default(5)->minValue(1)->maxValue(20)
                        ->helperText('Higher = more likely to be picked when multiple minigames are available.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(25)
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('sort')->label('#')->sortable(),
                TextColumn::make('key')->badge()->searchable()->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->description(fn (Minigame $record) => $record->description),
                TextColumn::make('gamepack.name')->label('Gamepack')->placeholder('Free')->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->updateStateUsing(fn (Minigame $record, bool $state) => app(MinigameService::class)->update($record, ['is_active' => $state])),
                TextColumn::make('settings.fixed_round')->label('Fixed rnd')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('gamepack')->relationship('gamepack', 'name')->preload(),
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->using(ServiceDelete::single(MinigameService::class)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->using(ServiceDelete::bulk(MinigameService::class)),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMinigames::route('/'),
            'create' => CreateMinigame::route('/create'),
            'edit' => EditMinigame::route('/{record}/edit'),
        ];
    }
}
