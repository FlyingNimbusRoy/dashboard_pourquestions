<?php

namespace App\Filament\Resources\Achievements;

use App\Enums\AchievementCriteria;
use App\Filament\Resources\Achievements\Pages\CreateAchievement;
use App\Filament\Resources\Achievements\Pages\EditAchievement;
use App\Filament\Resources\Achievements\Pages\ListAchievements;
use App\Models\Achievement;
use App\Models\Character;
use App\Services\Crud\AchievementService;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use UnitEnum;

class AchievementResource extends Resource
{
    protected static ?string $model = Achievement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static string|UnitEnum|null $navigationGroup = 'Game setup';

    protected static ?int $navigationSort = 23;

    protected static ?string $modelLabel = 'achievement';

    protected static ?string $pluralModelLabel = 'achievements';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return app(AchievementService::class)->query()->with('character');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('image')->required()->maxLength(255),
            Textarea::make('description')->required()->columnSpanFull(),
            Select::make('criteria_type')->options(AchievementCriteria::class)->required(),
            TextInput::make('criteria_value')->numeric()->minValue(0)->required()->default(0),
            Toggle::make('exact_match'),
            Select::make('character_id')->label('Character')->options(fn () => Character::query()->orderBy('name')->pluck('name', 'id'))->searchable(),
            DateTimePicker::make('available_from'),
            DateTimePicker::make('available_until'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(25)
            ->defaultSort('name')
            ->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('character.name')->label('Character')->placeholder('-'),
            TextColumn::make('criteria_type')->badge(),
            TextColumn::make('criteria_value')->sortable(),
            IconColumn::make('exact_match')->boolean(),
            TextColumn::make('available_from')->dateTime()->placeholder('-')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('available_until')->dateTime()->placeholder('-')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            SelectFilter::make('criteria_type')->options(AchievementCriteria::class),
            TernaryFilter::make('exact_match')
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->using(fn (Achievement $record) => static::deleteViaService($record)),
            ]);
    }

    /** Delete through the CRUD service; service rule violations become a danger notification. */
    public static function deleteViaService(Achievement $record): bool
    {
        try {
            app(AchievementService::class)->delete($record);

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
            'index' => ListAchievements::route('/'),
            'create' => CreateAchievement::route('/create'),
            'edit' => EditAchievement::route('/{record}/edit'),
        ];
    }
}
