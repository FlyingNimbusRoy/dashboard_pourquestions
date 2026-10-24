<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\WorkQueue\SupportMessageResource;
use App\Models\SupportMessage;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/** Home widget: support messages currently assigned to the logged-in user. */
class MySupportMessages extends TableWidget
{
    protected static ?int $sort = 18;

    protected static ?string $heading = 'My support messages';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => SupportMessage::query()
                ->where('assignee_id', auth()->id())
                ->whereIn('status', ['in_progress', 'waiting'])
                ->orderBy('created_at'))
            ->columns([
                TextColumn::make('type')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'bug'      => 'danger',
                        'feedback' => 'info',
                        default    => 'warning',
                    }),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_progress' => 'primary',
                        'waiting'     => 'info',
                        default       => 'gray',
                    }),
                TextColumn::make('message')
                    ->label('Message')
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since(),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (SupportMessage $record): string => SupportMessageResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated(false)
            ->emptyStateHeading('No messages assigned to you')
            ->emptyStateDescription('Assign a support message to yourself from the Support messages list.');
    }
}
