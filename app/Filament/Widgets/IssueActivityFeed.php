<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Issues\IssueResource;
use App\Models\DashIssueActivity;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class IssueActivityFeed extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 9;

    protected static ?string $heading = 'Recent issue activity';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => DashIssueActivity::query()->with('issue')->latest('id')->limit(10))
            ->columns([
                TextColumn::make('created_at')->label('When')->since(),
                TextColumn::make('action')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'reopened' => 'warning',
                        'resolved' => 'success',
                        'wont_fix' => 'gray',
                        default    => 'info',
                    }),
                TextColumn::make('from_value')->label('From')->placeholder('—'),
                TextColumn::make('to_value')->label('To')->placeholder('—'),
                TextColumn::make('issue.reason_summary')
                    ->label('Issue')
                    ->limit(50)
                    ->url(fn (DashIssueActivity $r) => $r->issue_id
                        ? IssueResource::getUrl('view', ['record' => $r->issue_id])
                        : null),
            ])
            ->paginated(false)
            ->emptyStateHeading('No issue activity yet');
    }
}
