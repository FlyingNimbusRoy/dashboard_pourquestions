<?php

namespace App\Filament\Resources\WorkQueue;

use App\Filament\Resources\WorkQueue\Pages\ListSupportMessages;
use App\Filament\Resources\WorkQueue\Pages\ViewSupportMessage;
use App\Models\SupportMessage;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class SupportMessageResource extends Resource
{
    protected static ?string $model = SupportMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Work queue';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'support message';

    protected static ?string $pluralModelLabel = 'support messages';

    public static function table(Table $table): Table
    {
        return $table
            ->paginationPageOptions([10, 25, 50])
            ->defaultPaginationPageOption(25)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->colorUsing(fn (string $state): string => match ($state) {
                        'bug'      => 'danger',
                        'feedback' => 'info',
                        'support'  => 'warning',
                        default    => 'gray',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->colorUsing(fn (string $state): string => match ($state) {
                        'new'         => 'warning',
                        'in_progress' => 'primary',
                        'waiting'     => 'info',
                        'resolved'    => 'success',
                        default       => 'gray',
                    }),
                TextColumn::make('player.name')
                    ->label('Player')
                    ->placeholder('Anonymous'),
                TextColumn::make('message')
                    ->limit(80)
                    ->wrap(),
                TextColumn::make('assignee.name')
                    ->label('Assignee')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'feedback' => 'Feedback',
                        'bug'      => 'Bug report',
                        'support'  => 'Support',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'new'         => 'New',
                        'in_progress' => 'In progress',
                        'waiting'     => 'Waiting',
                        'resolved'    => 'Resolved',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSupportMessages::route('/'),
            'view'  => ViewSupportMessage::route('/{record}'),
        ];
    }
}
