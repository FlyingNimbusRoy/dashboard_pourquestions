<?php

namespace App\Filament\Resources\Issues;

use App\Filament\Resources\Issues\Pages\ListIssues;
use App\Filament\Resources\Issues\Pages\ViewIssue;
use App\Models\DashIssue;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class IssueResource extends Resource
{
    protected static ?string $model = DashIssue::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static string|UnitEnum|null $navigationGroup = 'Work queue';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'reason_summary';

    public static function table(Table $table): Table
    {
        $userNames = User::pluck('name', 'id');

        return $table
            ->paginationPageOptions([10, 25, 50])
            ->defaultPaginationPageOption(25)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'content' => 'info',
                        'balance' => 'warning',
                        'tech' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(fn (string $state, DashIssue $record): string =>
                        $record->subject_id ? "{$state} #{$record->subject_id}" : $state
                    ),
                TextColumn::make('reason_summary')
                    ->limit(70)
                    ->wrap(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'warning',
                        'assigned' => 'info',
                        'in_progress' => 'primary',
                        'resolved' => 'success',
                        'wont_fix' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('assignee_id')
                    ->label('Assignee')
                    ->formatStateUsing(fn ($state): string =>
                        $state ? ($userNames[$state] ?? "User #{$state}") : '—'
                    )
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Raised')
                    ->since()
                    ->sortable(),
            ])
            ->emptyStateHeading('No issues')
            ->emptyStateDescription('Issues are raised automatically by threshold checks, or manually with "New issue".')
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'content' => 'Content',
                        'balance' => 'Balance',
                        'tech' => 'Tech',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'assigned' => 'Assigned',
                        'in_progress' => 'In progress',
                        'resolved' => 'Resolved',
                        'wont_fix' => "Won't fix",
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
            'index' => ListIssues::route('/'),
            'view' => ViewIssue::route('/{record}'),
        ];
    }
}
