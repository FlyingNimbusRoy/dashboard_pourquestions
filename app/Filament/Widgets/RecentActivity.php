<?php

namespace App\Filament\Widgets;

use App\Models\QuestionChange;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentActivity extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 8;

    protected static ?string $heading = 'Recent question changes';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => QuestionChange::query()->with('user')->latest('id')->limit(10))
            ->columns([
                TextColumn::make('created_at')->label('When')->since(),
                TextColumn::make('user.name')->label('Who')->placeholder('system'),
                TextColumn::make('action')->badge(),
                TextColumn::make('question_id')->label('Question')->prefix('#')
                    ->url(fn (QuestionChange $r) => \App\Filament\Resources\Questions\QuestionResource::getUrl('edit', ['record' => $r->question_id])),
            ])
            ->paginated(false)
            ->emptyStateHeading('No changes yet');
    }
}
