<?php

namespace App\Filament\Resources\Gamestats;

use App\Filament\Resources\Gamestats\Pages\EditGamestats;
use App\Filament\Resources\Gamestats\Pages\ListGamestats;
use App\Filament\Resources\Gamestats\Pages\ViewGamestats;
use App\Models\UserGamestats;
use App\Services\Crud\UserGamestatsService;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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

class GamestatsResource extends Resource
{
    protected static ?string $model = UserGamestats::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Game';

    protected static ?int $navigationSort = 29;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $modelLabel = 'gamestats';

    protected static ?string $pluralModelLabel = 'gamestats';

    public static function getEloquentQuery(): Builder
    {
        return app(UserGamestatsService::class)->query()->with('user:id,name');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('user.name')->label('Player')->disabled(),
            TextInput::make('games_played')->numeric()->minValue(0)->required(),
            TextInput::make('games_won')->numeric()->minValue(0)->required(),
            TextInput::make('games_lost')->numeric()->minValue(0)->required(),
            TextInput::make('total_shots')->numeric()->minValue(0)->required(),
            TextInput::make('most_shots')->numeric()->minValue(0)->required(),
            TextInput::make('least_shots')->numeric()->minValue(0),
            TextInput::make('shot_highscore')->numeric()->minValue(0)->required(),
            TextInput::make('question_highscore')->numeric()->minValue(0)->required(),
            Textarea::make('modifiers')->rows(6)->columnSpanFull()
                ->helperText('JSON')
                ->rule('json')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                ->dehydrateStateUsing(fn ($state) => filled($state) ? json_decode($state, true) : null),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(3)->columnSpanFull()->schema([
                TextEntry::make('user.name')->label('Player'),
                TextEntry::make('games_played'), TextEntry::make('games_won'), TextEntry::make('games_lost'),
                TextEntry::make('total_shots'), TextEntry::make('most_shots'),
                TextEntry::make('least_shots')->placeholder('-'),
                TextEntry::make('shot_highscore'), TextEntry::make('question_highscore'),
                TextEntry::make('modifiers')->columnSpanFull()->placeholder('-')
                    ->state(fn (UserGamestats $record) => $record->modifiers === null ? null : json_encode($record->modifiers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                    ->extraAttributes(['class' => 'whitespace-pre-wrap font-mono text-xs']),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(25)
            ->defaultSort('id')
            ->columns([
            TextColumn::make('user.name')->label('Player')->searchable(),
            TextColumn::make('games_played')->sortable(),
            TextColumn::make('games_won')->sortable(),
            TextColumn::make('games_lost')->sortable(),
            TextColumn::make('total_shots')->sortable(),
            TextColumn::make('shot_highscore')->sortable(),
            TextColumn::make('question_highscore')->sortable(),
            ])
            ->filters([
            
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
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
            'index' => ListGamestats::route('/'),
            'view' => ViewGamestats::route('/{record}'),
            'edit' => EditGamestats::route('/{record}/edit'),
        ];
    }
}
