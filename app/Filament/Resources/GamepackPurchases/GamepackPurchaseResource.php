<?php

namespace App\Filament\Resources\GamepackPurchases;

use App\Filament\Resources\GamepackPurchases\Pages\CreateGamepackPurchase;
use App\Filament\Resources\GamepackPurchases\Pages\ListGamepackPurchases;
use App\Models\Gamepack;
use App\Models\GamepackPurchase;
use App\Models\User;
use App\Services\Crud\GamepackPurchaseService;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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

class GamepackPurchaseResource extends Resource
{
    protected static ?string $model = GamepackPurchase::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static string|UnitEnum|null $navigationGroup = 'Game';

    protected static ?int $navigationSort = 24;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $modelLabel = 'gamepack purchase';

    protected static ?string $pluralModelLabel = 'gamepack purchases';

    public static function getEloquentQuery(): Builder
    {
        return app(GamepackPurchaseService::class)->query()->with(['user:id,name', 'gamepack:id,name']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Select::make('user_id')->label('Player')->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))->searchable()->required(),
            Select::make('gamepack_id')->label('Gamepack')->options(fn () => Gamepack::query()->orderBy('name')->pluck('name', 'id'))->searchable()->required(),
            Toggle::make('opened'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(25)
            ->defaultSort('created_at', 'desc')
            ->columns([
            TextColumn::make('user.name')->label('Player')->searchable(),
            TextColumn::make('gamepack.name')->label('Gamepack')->searchable(),
            IconColumn::make('opened')->boolean(),
            IconColumn::make('access_through_admin_grant')->label('Admin grant')->boolean(),
            TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
            SelectFilter::make('gamepack_id')->label('Gamepack')->options(fn () => Gamepack::query()->orderBy('name')->pluck('name', 'id')),
            TernaryFilter::make('opened'),
            TernaryFilter::make('access_through_admin_grant')->label('Admin grant')
            ])
            ->recordActions([
                DeleteAction::make()->label('Revoke')->using(fn (GamepackPurchase $record) => static::deleteViaService($record)),
            ]);
    }

    /** Delete through the CRUD service; service rule violations become a danger notification. */
    public static function deleteViaService(GamepackPurchase $record): bool
    {
        try {
            app(GamepackPurchaseService::class)->delete($record);

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
            'index' => ListGamepackPurchases::route('/'),
            'create' => CreateGamepackPurchase::route('/create'),
        ];
    }
}
