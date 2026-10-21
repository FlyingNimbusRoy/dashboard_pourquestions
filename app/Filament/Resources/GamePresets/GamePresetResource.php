<?php

namespace App\Filament\Resources\GamePresets;

use App\Filament\Resources\GamePresets\Pages\CreateGamePreset;
use App\Filament\Resources\GamePresets\Pages\EditGamePreset;
use App\Filament\Resources\GamePresets\Pages\ListGamePresets;
use App\Models\GamePreset;
use App\Models\User;
use App\Services\Crud\GamePresetService;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use UnitEnum;

class GamePresetResource extends Resource
{
    protected static ?string $model = GamePreset::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquare3Stack3d;

    protected static string|UnitEnum|null $navigationGroup = 'Game setup';

    protected static ?int $navigationSort = 25;

    protected static ?string $modelLabel = 'game preset';

    protected static ?string $pluralModelLabel = 'game presets';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return app(GamePresetService::class)->query()->with('user:id,name');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Select::make('user_id')->label('Player')->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))->searchable()->required(),
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('rounds')->numeric()->minValue(0),
            Toggle::make('is_nsfw')->label('NSFW'),
            Textarea::make('pack_ids')->rows(5)->columnSpanFull()
                ->helperText('JSON')
                ->rule('json')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                ->dehydrateStateUsing(fn ($state) => filled($state) ? json_decode($state, true) : null),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(25)
            ->defaultSort('id', 'desc')
            ->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('user.name')->label('Player'),
            TextColumn::make('rounds')->sortable(),
            IconColumn::make('is_nsfw')->label('NSFW')->boolean(),
            TextColumn::make('pack_ids')->label('Pack ids')->limit(60)->tooltip(fn ($record) => json_encode($record->pack_ids))
                ->state(fn ($record) => $record->pack_ids === null ? null : json_encode($record->pack_ids, JSON_UNESCAPED_UNICODE)),
            ])
            ->filters([
            TernaryFilter::make('is_nsfw')->label('NSFW')
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->using(fn (GamePreset $record) => static::deleteViaService($record)),
            ]);
    }

    /** Delete through the CRUD service; service rule violations become a danger notification. */
    public static function deleteViaService(GamePreset $record): bool
    {
        try {
            app(GamePresetService::class)->delete($record);

            return true;
        } catch (ValidationException $e) {
            Notification::make()->danger()->title(collect($e->errors())->flatten()->implode(' '))->send();

            return false;
        }
    }

    public static function getRelations(): array
    {
        return [
            
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGamePresets::route('/'),
            'create' => CreateGamePreset::route('/create'),
            'edit' => EditGamePreset::route('/{record}/edit'),
        ];
    }
}
