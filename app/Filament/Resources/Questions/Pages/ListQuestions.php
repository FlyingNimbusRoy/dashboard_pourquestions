<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\QuestionResource;
use App\Models\Question;
use App\Services\Insights\QuestionUsage;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /** Status presets with live counts; each tab is a filter on the same table, rows open the editor. */
    public function getTabs(): array
    {
        $count = fn (callable $scope) => $scope(Question::query())->count();
        $neverAsked = fn () => app(QuestionUsage::class)->neverAskedIds();

        return [
            'all' => Tab::make('All')
                ->badge(Question::query()->count()),
            'no_category' => Tab::make('No category')
                ->badge($count(fn (Builder $q) => $q->whereNull('category_id')))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('category_id')),
            'no_gamepack' => Tab::make('No gamepack')
                ->badge($count(fn (Builder $q) => $q->whereNull('gamepack_id')))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('gamepack_id')),
            'never_validated' => Tab::make('Never validated')
                ->badge($count(fn (Builder $q) => $q->where('last_validated', '<=', Question::NEVER_VALIDATED)))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('last_validated', '<=', Question::NEVER_VALIDATED)),
            'due' => Tab::make('Due for review')
                ->badge($count(fn (Builder $q) => $q->validationDue()))
                ->modifyQueryUsing(fn (Builder $query) => $query->validationDue()),
            'never_asked' => Tab::make('Never asked')
                ->badge(count($neverAsked()))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('questions.id', $neverAsked())),
            'grading_due' => Tab::make('Grading due')
                ->badge($count(fn (Builder $q) => $q->gradingDue()))
                ->modifyQueryUsing(fn (Builder $query) => $query->gradingDue()->reorder()->orderBy('updated_at', 'asc')),
        ];
    }
}
