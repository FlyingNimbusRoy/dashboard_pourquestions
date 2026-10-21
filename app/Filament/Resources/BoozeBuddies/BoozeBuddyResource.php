<?php

namespace App\Filament\Resources\BoozeBuddies;

use App\Enums\BuddyStatus;
use App\Filament\Resources\BoozeBuddies\Pages\EditBoozeBuddy;
use App\Filament\Resources\BoozeBuddies\Pages\ListBoozeBuddies;
use App\Models\BoozeBuddy;
use App\Services\Crud\BoozeBuddyService;
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

class BoozeBuddyResource extends Resource
{
    protected static ?string $model = BoozeBuddy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Game setup';

    protected static ?int $navigationSort = 27;

    protected static ?string $modelLabel = 'booze buddy';

    protected static ?string $pluralModelLabel = 'booze buddies';

    public static function getEloquentQuery(): Builder
    {
        return app(BoozeBuddyService::class)->query()->with(['sender:id,name', 'receiver:id,name']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('sender.name')->label('Sender')->disabled(),
            TextInput::make('receiver.name')->label('Receiver')->disabled(),
            Select::make('status')->options(BuddyStatus::class)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(25)
            ->defaultSort('id', 'desc')
            ->columns([
            TextColumn::make('sender.name')->label('Sender'),
            TextColumn::make('receiver.name')->label('Receiver'),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
            SelectFilter::make('status')->options(BuddyStatus::class)
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->using(fn (BoozeBuddy $record) => static::deleteViaService($record)),
            ]);
    }

    /** Delete through the CRUD service; service rule violations become a danger notification. */
    public static function deleteViaService(BoozeBuddy $record): bool
    {
        try {
            app(BoozeBuddyService::class)->delete($record);

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
            'index' => ListBoozeBuddies::route('/'),
            'edit' => EditBoozeBuddy::route('/{record}/edit'),
        ];
    }
}
