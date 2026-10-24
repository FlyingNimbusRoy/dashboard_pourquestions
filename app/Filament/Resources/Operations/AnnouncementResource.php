<?php

namespace App\Filament\Resources\Operations;

use App\Filament\Resources\Operations\Pages\CreateAnnouncement;
use App\Filament\Resources\Operations\Pages\EditAnnouncement;
use App\Filament\Resources\Operations\Pages\ListAnnouncements;
use App\Models\Announcement;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
use UnitEnum;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = 'Game setup';

    protected static ?int $navigationSort = 11;

    protected static ?string $recordTitleAttribute = 'message';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Textarea::make('message')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),
                    Select::make('type')
                        ->required()
                        ->options(['info' => 'Info', 'warning' => 'Warning', 'maintenance' => 'Maintenance']),
                    Toggle::make('active')
                        ->label('Active')
                        ->default(true)
                        ->inline(false),
                    DateTimePicker::make('starts_at')
                        ->label('Starts at')
                        ->nullable()
                        ->helperText('Leave blank to show immediately'),
                    DateTimePicker::make('ends_at')
                        ->label('Ends at')
                        ->nullable()
                        ->helperText('Leave blank to show until manually deactivated'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->paginationPageOptions([10, 25, 50])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->colorUsing(fn (string $state) => match ($state) {
                        'info'        => 'info',
                        'warning'     => 'warning',
                        'maintenance' => 'danger',
                        default       => 'gray',
                    }),
                TextColumn::make('message')
                    ->limit(80)
                    ->wrap(),
                ToggleColumn::make('active')
                    ->updateStateUsing(fn (Announcement $record, bool $state) => $record->update(['active' => $state])),
                TextColumn::make('starts_at')
                    ->since()
                    ->placeholder('Immediately')
                    ->label('Starts'),
                TextColumn::make('ends_at')
                    ->since()
                    ->placeholder('Never')
                    ->label('Ends'),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(['info' => 'Info', 'warning' => 'Warning', 'maintenance' => 'Maintenance']),
                TernaryFilter::make('active'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAnnouncements::route('/'),
            'create' => CreateAnnouncement::route('/create'),
            'edit'   => EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
