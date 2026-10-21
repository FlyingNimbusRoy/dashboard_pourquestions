<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use App\Filament\Resources\Questions\Support\ServiceDelete;
use App\Services\Crud\CategoryService;
use App\Services\Insights\QuestionUsage;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(25)
            ->defaultSort('name')
            ->columns([
                ColorColumn::make('color')
                    ->label(''),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Category $record) => $record->description),
                TextColumn::make('questions_count')
                    ->label('Questions')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('due_count')
                    ->label('Due for review')
                    ->numeric()
                    ->sortable()
                    ->color(fn (int $state) => $state > 0 ? 'warning' : 'success'),
                TextColumn::make('questions_avg_difficulty')
                    ->label('Avg. difficulty')
                    ->numeric(decimalPlaces: 1)
                    ->sortable(),
                TextColumn::make('pct_validated')
                    ->label('% validated')
                    ->state(fn (Category $record) => $record->questions_count > 0 ? round($record->valid_count / $record->questions_count * 100).'%' : '-')
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderByRaw('(valid_count / NULLIF(questions_count, 0)) '.($direction === 'desc' ? 'desc' : 'asc'))),
                TextColumn::make('pct_asked')
                    ->label('% ever asked')
                    ->state(fn (Category $record) => $record->questions_count > 0 ? (self::askedPct()[$record->id] ?? 0).'%' : '-')
                    ->sortable(query: function (Builder $query, string $direction) {
                        $cases = collect(self::askedPct())->map(fn ($pct, $id) => 'when '.(int) $id.' then '.(float) $pct)->implode(' ');

                        return $query->orderByRaw($cases === '' ? '0' : "(case categories.id {$cases} else 0 end) ".($direction === 'desc' ? 'desc' : 'asc'));
                    }),
            ])
            ->emptyStateHeading('No categories yet')
            ->emptyStateDescription('Categories group questions so games stay balanced. Create the first one.')
            ->emptyStateIcon(Heroicon::OutlinedTag)
            ->emptyStateActions([CreateAction::make()])
            ->recordActions([
                EditAction::make(),
                Action::make('merge')
                    ->label('Merge into…')
                    ->icon(Heroicon::OutlinedArrowsRightLeft)
                    ->color('gray')
                    ->modalDescription('Moves every question of this category into the chosen category. This category is kept (empty) so you can delete it afterwards.')
                    ->schema([
                        Select::make('target')
                            ->label('Target category')
                            ->options(fn (Category $record) => Category::whereKeyNot($record->id)->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (Category $record, array $data, CategoryService $service) {
                        $moved = $service->merge($record, Category::findOrFail($data['target']));
                        Notification::make()->success()->title("Moved {$moved} questions.")->send();
                    })
                    ->visible(fn (Category $record) => $record->questions_count > 0),
                DeleteAction::make()
                    ->modalDescription(fn (Category $record) => $record->questions_count > 0
                        ? "This category still has {$record->questions_count} questions. Choose the category they move to; then the category is deleted."
                        : 'Are you sure you would like to do this?')
                    ->schema([
                        Select::make('target')
                            ->label('Move the questions to')
                            ->options(fn (Category $record) => Category::whereKeyNot($record->id)->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->visible(fn (Category $record) => $record->questions_count > 0)
                            ->required(fn (Category $record) => $record->questions_count > 0),
                    ])
                    ->using(function (Category $record, array $data, CategoryService $service): bool {
                        if ($record->questions_count > 0) {
                            $service->merge($record, Category::findOrFail($data['target']));
                        }

                        return ServiceDelete::single(CategoryService::class)($record);
                    }),
            ]);
    }

    /** @return array<int, float> category id => % of its questions asked at least once */
    private static function askedPct(): array
    {
        return collect(app(QuestionUsage::class)->perCategory())
            ->filter(fn (array $row) => $row['id'] !== null)
            ->mapWithKeys(fn (array $row) => [$row['id'] => $row['pct']])
            ->all();
    }
}
