<?php

namespace App\Filament\Resources\Modifiers;

use App\Filament\Resources\Modifiers\Pages\CreateModifier;
use App\Filament\Resources\Modifiers\Pages\EditModifier;
use App\Filament\Resources\Modifiers\Pages\ListModifiers;
use App\Filament\Resources\Questions\Support\ServiceDelete;
use App\Models\Modifier;
use App\Services\Crud\ModifierService;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ModifierResource extends Resource
{
    protected static ?string $model = Modifier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return app(ModifierService::class)->query();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')->required()->maxLength(255),
                    TextInput::make('icon')->maxLength(500),
                    Textarea::make('description')->rows(3)->columnSpanFull(),
                    Select::make('coupled_gamepack_id')
                        ->label('Coupled gamepack')
                        ->relationship('gamepack', 'name')
                        ->searchable()
                        ->preload(),
                    Toggle::make('turnbased')->inline(false),
                    Toggle::make('is_active')->label('Active')->default(true)->inline(false),
                    CodeEditor::make('effects')
                        ->language(Language::Json)
                        ->helperText('JSON object read by the game, for example {"onShot": {"multiplier": 2}}.')
                        ->rule('json')
                        ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $state)
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? json_decode($state, true) : null)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(25)
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->description(fn (Modifier $record) => $record->description),
                TextColumn::make('gamepack.name')->label('Gamepack')->placeholder('None')->sortable(),
                IconColumn::make('turnbased')->boolean(),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->filters([
                SelectFilter::make('gamepack')->relationship('gamepack', 'name')->preload(),
                TernaryFilter::make('is_active')->label('Active'),
                TernaryFilter::make('turnbased'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->using(ServiceDelete::single(ModifierService::class)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->using(ServiceDelete::bulk(ModifierService::class)),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListModifiers::route('/'),
            'create' => CreateModifier::route('/create'),
            'edit' => EditModifier::route('/{record}/edit'),
        ];
    }
}
