<?php

namespace App\Filament\Resources\QuestionSimilarities\Pages;

use App\Enums\SimilarityStatus;
use App\Filament\Resources\QuestionSimilarities\QuestionSimilarityResource;
use App\Jobs\DetectSimilarQuestionsJob;
use App\Models\QuestionSimilarity;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListQuestionSimilarities extends ListRecords
{
    protected static string $resource = QuestionSimilarityResource::class;

    public function getSubheading(): string
    {
        return 'Checked automatically every hour. Decide per pair: not a duplicate, or delete one of the two. Decided pairs disappear, so the next pair is always on top.';
    }

    public function getTabs(): array
    {
        $count = fn (SimilarityStatus $status) => QuestionSimilarity::where('status', $status)->count();

        return [
            'open' => Tab::make('Open')
                ->badge($count(SimilarityStatus::Open))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SimilarityStatus::Open)),
            'dismissed' => Tab::make('Dismissed')
                ->badge($count(SimilarityStatus::Dismissed))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SimilarityStatus::Dismissed)),
            'resolved' => Tab::make('Resolved')
                ->badge($count(SimilarityStatus::Resolved))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SimilarityStatus::Resolved)),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('detect')
                ->label('Run check now')
                ->icon(Heroicon::OutlinedArrowPath)
                ->schema([
                    Toggle::make('full')
                        ->label('Compare all questions')
                        ->helperText('Off: only questions changed since the last check (fast).'),
                ])
                ->action(function (array $data) {
                    DetectSimilarQuestionsJob::dispatch((bool) $data['full']);
                    Notification::make()->success()->title('Similarity check started')->body('Refresh this page in a minute.')->send();
                }),
        ];
    }
}
