<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Issues\IssueResource;
use App\Models\DashIssue;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class MyAssignedIssues extends TableWidget
{
    protected static ?int $sort = 19;

    protected static ?string $heading = 'My assigned issues';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => DashIssue::query()
                ->where('assignee_id', auth()->id())
                ->whereIn('status', ['assigned', 'in_progress'])
                ->orderBy('created_at'))
            ->columns([
                TextColumn::make('type')->badge()
                    ->colorUsing(fn (string $state): string => match ($state) {
                        'content' => 'info',
                        'balance' => 'warning',
                        'tech'    => 'danger',
                        default   => 'gray',
                    }),
                TextColumn::make('status')->badge()
                    ->colorUsing(fn (string $state): string => match ($state) {
                        'assigned'    => 'info',
                        'in_progress' => 'primary',
                        default       => 'gray',
                    }),
                TextColumn::make('reason_summary')
                    ->label('Summary')
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Raised')
                    ->since(),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (DashIssue $record): string => IssueResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated(false)
            ->emptyStateHeading('No issues assigned to you')
            ->emptyStateDescription('Assign an issue to yourself from the Issues list.');
    }
}
