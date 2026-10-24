<?php

namespace App\Filament\Resources\Operations;

use App\Filament\Resources\Operations\Pages\CreateFeatureToggle;
use App\Filament\Resources\Operations\Pages\EditFeatureToggle;
use App\Filament\Resources\Operations\Pages\ListFeatureToggles;
use App\Models\FeatureToggle;
use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class FeatureToggleResource extends Resource
{
    protected static ?string $model = FeatureToggle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static string|UnitEnum|null $navigationGroup = 'Game setup';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'key';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Select::make('type')
                        ->required()
                        ->options(['minigame' => 'Minigame', 'modifier' => 'Modifier']),
                    TextInput::make('key')
                        ->required()
                        ->maxLength(64)
                        ->readOnly(fn (string $operation) => $operation === 'edit'),
                    Toggle::make('enabled')
                        ->default(true)
                        ->inline(false),
                    Textarea::make('reason')
                        ->nullable()
                        ->rows(2)
                        ->columnSpanFull(),
                    TextInput::make('issue_id')
                        ->label('Issue ID')
                        ->numeric()
                        ->nullable()
                        ->helperText('Optional link to a dash_issues row'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $userNames = User::pluck('name', 'id');

        return $table
            ->defaultSort('type')
            ->paginationPageOptions([10, 25, 50])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'minigame' => 'info',
                        'modifier' => 'warning',
                        default    => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('key')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('enabled')
                    ->updateStateUsing(fn (FeatureToggle $record, bool $state) => $record->update([
                        'enabled'    => $state,
                        'changed_by' => auth()->id(),
                    ])),
                TextColumn::make('reason')
                    ->limit(60)
                    ->placeholder('—'),
                TextColumn::make('changed_by')
                    ->label('Changed by')
                    ->formatStateUsing(fn ($state) => $state ? ($userNames[$state] ?? "User #{$state}") : '—'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(['minigame' => 'Minigame', 'modifier' => 'Modifier']),
                TernaryFilter::make('enabled')
                    ->label('Enabled'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListFeatureToggles::route('/'),
            'create' => CreateFeatureToggle::route('/create'),
            'edit'   => EditFeatureToggle::route('/{record}/edit'),
        ];
    }
}
