<?php

namespace App\Filament\Resources\Gamepacks;

use App\Enums\GamepackCategory;
use App\Filament\Resources\Gamepacks\Pages\CreateGamepack;
use App\Filament\Resources\Gamepacks\Pages\EditGamepack;
use App\Filament\Resources\Gamepacks\Pages\ListGamepacks;
use App\Filament\Resources\Questions\Support\ServiceDelete;
use App\Models\Gamepack;
use App\Services\Crud\GamepackService;
use App\Services\Insights\QuestionUsage;
use BackedEnum;
use Illuminate\Support\Facades\DB;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class GamepackResource extends Resource
{
    protected static ?string $model = Gamepack::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return app(GamepackService::class)->query()->withCount(['questions', 'characters', 'modifiers', 'purchases'])
            ->addSelect([
                'owners_count' => DB::table('gamepack_purchased')->selectRaw('count(distinct user_id)')->whereColumn('gamepack_id', 'gamepacks.id'),
                'grants_count' => DB::table('gamepack_purchased')->selectRaw('count(distinct user_id)')->whereColumn('gamepack_id', 'gamepacks.id')->where('access_through_admin_grant', 1),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MinigamesRelationManager::class,
            RelationManagers\PurchasesRelationManager::class,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')->required()->maxLength(255),
                    Select::make('category')
                        ->options(GamepackCategory::class)
                        ->default(GamepackCategory::Gamepack)
                        ->required(),
                    Textarea::make('description')->rows(3)->columnSpanFull(),
                    TextInput::make('flavor_text')->maxLength(255)->columnSpanFull(),
                    TextInput::make('icon')->maxLength(255),
                    ColorPicker::make('color'),
                    TextInput::make('price')->numeric()->minValue(0)->prefix('EUR')->default(0)->required(),
                    TextInput::make('url_coverart')->label('Cover art URL')->maxLength(255),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(25)
            ->emptyStateHeading('No gamepacks yet')
            ->emptyStateDescription('Gamepacks bundle questions, characters and modifiers that players can buy. Create the first one.')
            ->emptyStateIcon(Heroicon::OutlinedRectangleStack)
            ->emptyStateActions([\Filament\Actions\CreateAction::make()])
            ->defaultSort('name')
            ->columns([
                ColorColumn::make('color')->label(''),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category')->badge()->sortable(),
                TextColumn::make('price')->money('EUR')->sortable(),
                TextColumn::make('questions_count')->label('Questions')->numeric()->sortable(),
                TextColumn::make('pct_asked')->label('% asked')
                    ->state(fn (Gamepack $record): string => (app(QuestionUsage::class)->forGamepack($record->id)['pct'] ?? 0).'%'),
                TextColumn::make('purchases_count')->label('Purchases')->numeric()->sortable(),
                TextColumn::make('owners_count')->label('Owners')->numeric()->sortable()
                    ->description(fn (Gamepack $record): ?string => $record->grants_count ? "{$record->grants_count} via admin grant" : null),
                TextColumn::make('characters_count')->label('Characters')->numeric()->sortable(),
                TextColumn::make('modifiers_count')->label('Modifiers')->numeric()->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')->options(GamepackCategory::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->using(ServiceDelete::single(GamepackService::class)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->using(ServiceDelete::bulk(GamepackService::class)),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGamepacks::route('/'),
            'create' => CreateGamepack::route('/create'),
            'edit' => EditGamepack::route('/{record}/edit'),
        ];
    }
}
