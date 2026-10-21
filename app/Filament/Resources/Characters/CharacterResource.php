<?php

namespace App\Filament\Resources\Characters;

use App\Filament\Resources\Characters\Pages\CreateCharacter;
use App\Filament\Resources\Characters\Pages\EditCharacter;
use App\Filament\Resources\Characters\Pages\ListCharacters;
use App\Filament\Resources\Questions\Support\ServiceDelete;
use App\Models\Character;
use App\Services\Crud\CharacterService;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CharacterResource extends Resource
{
    protected static ?string $model = Character::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Game setup';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return app(CharacterService::class)->query()->with('parent');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')->required()->maxLength(255),
                    TextInput::make('url')->label('URL / slug')->required()->maxLength(255),
                    Select::make('gamepack_id')
                        ->label('Gamepack')
                        ->relationship('gamepack', 'name')
                        ->searchable()
                        ->preload(),
                    Select::make('parent_character_id')
                        ->label('Parent character')
                        ->relationship('parent', 'name', fn (Builder $query, ?Character $record) => $record ? $query->whereKeyNot($record->getKey()) : $query)
                        ->searchable()
                        ->preload(),
                    Toggle::make('show_on_homepage')->default(true)->inline(false),
                ]),
            Section::make('Colors')
                ->columns(4)
                ->columnSpanFull()
                ->schema([
                    ColorPicker::make('color_primary')->label('Primary')->default('#6F1E51')->required(),
                    ColorPicker::make('color_secondary')->label('Secondary')->default('#1B1464')->required(),
                    ColorPicker::make('color_muted_primary')->label('Muted primary')->default('#ED4C67')->required(),
                    ColorPicker::make('color_muted_secondary')->label('Muted secondary')->default('#B53471')->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(25)
            ->defaultSort('name')
            ->columns([
                ColorColumn::make('color_primary')->label('1'),
                ColorColumn::make('color_secondary')->label('2'),
                ColorColumn::make('color_muted_primary')->label('3')->toggleable(isToggledHiddenByDefault: true),
                ColorColumn::make('color_muted_secondary')->label('4')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('gamepack.name')->label('Gamepack')->placeholder('None')->sortable(),
                TextColumn::make('parent.name')->label('Parent')->placeholder('None'),
                IconColumn::make('show_on_homepage')->label('Homepage')->boolean(),
            ])
            ->filters([
                SelectFilter::make('gamepack')->relationship('gamepack', 'name')->preload(),
                TernaryFilter::make('show_on_homepage')->label('On homepage'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->using(ServiceDelete::single(CharacterService::class)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->using(ServiceDelete::bulk(CharacterService::class)),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCharacters::route('/'),
            'create' => CreateCharacter::route('/create'),
            'edit' => EditCharacter::route('/{record}/edit'),
        ];
    }
}
