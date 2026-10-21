<?php

namespace App\Filament\Resources\SetupPresets;

use App\Filament\Resources\SetupPresets\Pages\CreateSetupPreset;
use App\Filament\Resources\SetupPresets\Pages\EditSetupPreset;
use App\Filament\Resources\SetupPresets\Pages\ListSetupPresets;
use App\Models\SetupPreset;
use App\Models\User;
use App\Services\Crud\SetupPresetService;
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

class SetupPresetResource extends Resource
{
    protected static ?string $model = SetupPreset::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Game setup';

    protected static ?int $navigationSort = 26;

    protected static ?string $modelLabel = 'setup preset';

    protected static ?string $pluralModelLabel = 'setup presets';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return app(SetupPresetService::class)->query()->with('user:id,name');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Select::make('user_id')->label('Player')->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))->searchable()->required(),
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('game_length')->numeric()->minValue(0),
            Toggle::make('is_nsfw')->label('NSFW'),
            Toggle::make('burst_mode'),
            Textarea::make('excluded_packs')->rows(4)->columnSpanFull()
                ->helperText('JSON')
                ->rule('json')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                ->dehydrateStateUsing(fn ($state) => filled($state) ? json_decode($state, true) : null),
            Textarea::make('excluded_categories')->rows(4)->columnSpanFull()
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
            TextColumn::make('game_length')->sortable(),
            IconColumn::make('is_nsfw')->label('NSFW')->boolean(),
            IconColumn::make('burst_mode')->boolean(),
            TextColumn::make('excluded_packs')->label('Excluded packs')->limit(60)->tooltip(fn ($record) => json_encode($record->excluded_packs))
                ->state(fn ($record) => $record->excluded_packs === null ? null : json_encode($record->excluded_packs, JSON_UNESCAPED_UNICODE)),
            TextColumn::make('excluded_categories')->label('Excluded categories')->limit(60)->tooltip(fn ($record) => json_encode($record->excluded_categories))
                ->state(fn ($record) => $record->excluded_categories === null ? null : json_encode($record->excluded_categories, JSON_UNESCAPED_UNICODE)),
            ])
            ->filters([
            TernaryFilter::make('is_nsfw')->label('NSFW'),
            TernaryFilter::make('burst_mode')
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->using(fn (SetupPreset $record) => static::deleteViaService($record)),
            ]);
    }

    /** Delete through the CRUD service; service rule violations become a danger notification. */
    public static function deleteViaService(SetupPreset $record): bool
    {
        try {
            app(SetupPresetService::class)->delete($record);

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
            'index' => ListSetupPresets::route('/'),
            'create' => CreateSetupPreset::route('/create'),
            'edit' => EditSetupPreset::route('/{record}/edit'),
        ];
    }
}
