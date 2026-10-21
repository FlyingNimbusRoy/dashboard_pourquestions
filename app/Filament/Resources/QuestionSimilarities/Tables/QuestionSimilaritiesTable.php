<?php

namespace App\Filament\Resources\QuestionSimilarities\Tables;

use App\Enums\SimilarityStatus;
use App\Filament\Resources\Questions\QuestionResource;
use App\Models\Question;
use App\Models\QuestionSimilarity;
use App\Services\Crud\QuestionService;
use App\Services\Insights\QuestionUsage;
use Illuminate\Validation\ValidationException;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;

class QuestionSimilaritiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferLoading()->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(25)
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'question.answers', 'question.category', 'similarQuestion.answers', 'similarQuestion.category', 'reviewer',
            ]))
            ->defaultSort('score', 'desc')
            ->searchPlaceholder('Search question text or #id')
            ->emptyStateHeading(fn ($livewire) => match (data_get($livewire, 'activeTab')) {
                'dismissed' => 'No dismissed pairs',
                'resolved' => 'No resolved pairs',
                default => 'No open duplicates',
            })
            ->emptyStateDescription(fn ($livewire) => match (data_get($livewire, 'activeTab')) {
                'dismissed', 'resolved' => 'Pairs you decide on show up here.',
                default => 'The hourly check found nothing to decide. You can also compare all questions now with "Run check now".',
            })
            ->emptyStateIcon(Heroicon::OutlinedSparkles)
            ->columns([
                TextColumn::make('score')
                    ->badge()
                    ->formatStateUsing(fn (float $state) => round($state * 100).'%')
                    ->color(fn (QuestionSimilarity $record) => match (true) {
                        $record->isLikelyDuplicate() => 'danger',
                        $record->score >= 0.7 => 'warning',
                        default => 'gray',
                    })
                    ->description(fn (QuestionSimilarity $record) => match (true) {
                        $record->isLikelyDuplicate() => 'Likely duplicate',
                        $record->score >= 0.7 => 'Similar',
                        default => 'Related',
                    })
                    ->sortable(),
                TextColumn::make('question.vraag')
                    ->label('Question A')
                    ->html()
                    ->formatStateUsing(fn (QuestionSimilarity $record) => self::questionCell($record->question))
                    ->searchable(query: fn (Builder $query, string $search) => self::search($query, $search))
                    ->wrap(),
                TextColumn::make('similarQuestion.vraag')
                    ->label('Question B')
                    ->html()
                    ->formatStateUsing(fn (QuestionSimilarity $record) => self::questionCell($record->similarQuestion))
                    ->wrap(),
                TextColumn::make('signals')
                    ->label('Why')
                    ->state(fn (QuestionSimilarity $record) => self::explain($record))
                    ->html()
                    ->wrap(),
                TextColumn::make('status')
                    ->badge()
                    ->description(fn (QuestionSimilarity $record) => $record->reviewer?->name),
            ])
            ->filters([
                SelectFilter::make('strength')
                    ->options(['duplicate' => 'Likely duplicate', 'similar' => 'Similar or stronger'])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'duplicate' => $query->where('score', '>=', config('dashboard.similarity.duplicate_threshold')),
                        'similar' => $query->where('score', '>=', 0.7),
                        default => $query,
                    }),
            ])
            ->recordActions([
                Action::make('dismiss')
                    ->label('Not a duplicate')
                    ->icon(Heroicon::OutlinedHandThumbUp)
                    ->color('gray')
                    ->action(fn (QuestionSimilarity $record) => self::decide($record, SimilarityStatus::Dismissed))
                    ->visible(fn (QuestionSimilarity $record) => $record->status === SimilarityStatus::Open),
                self::deleteAction('deleteA', 'Delete A', fn (QuestionSimilarity $r) => $r->question),
                self::deleteAction('deleteB', 'Delete B', fn (QuestionSimilarity $r) => $r->similarQuestion),
                ActionGroup::make([
                    Action::make('resolve')
                        ->label('Mark as resolved')
                        ->icon(Heroicon::OutlinedCheck)
                        ->tooltip('E.g. after rewording one of them so they no longer overlap.')
                        ->action(fn (QuestionSimilarity $record) => self::decide($record, SimilarityStatus::Resolved)),
                    Action::make('editA')
                        ->label('Edit question A')
                        ->icon(Heroicon::OutlinedPencilSquare)
                        ->url(fn (QuestionSimilarity $record) => QuestionResource::getUrl('edit', ['record' => $record->question_id]))
                        ->openUrlInNewTab(),
                    Action::make('editB')
                        ->label('Edit question B')
                        ->icon(Heroicon::OutlinedPencilSquare)
                        ->url(fn (QuestionSimilarity $record) => QuestionResource::getUrl('edit', ['record' => $record->similar_question_id]))
                        ->openUrlInNewTab(),
                    Action::make('reopen')
                        ->icon(Heroicon::OutlinedArrowUturnLeft)
                        ->action(fn (QuestionSimilarity $record) => $record->update(['status' => SimilarityStatus::Open, 'reviewed_by' => null, 'reviewed_at' => null]))
                        ->visible(fn (QuestionSimilarity $record) => $record->status !== SimilarityStatus::Open),
                ]),
            ])
            ->toolbarActions([
                BulkAction::make('dismiss')
                    ->label('Not duplicates')
                    ->icon(Heroicon::OutlinedHandThumbUp)
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each(fn (QuestionSimilarity $record) => self::decide($record, SimilarityStatus::Dismissed)))
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    private static function decide(QuestionSimilarity $record, SimilarityStatus $status): void
    {
        $record->update(['status' => $status, 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
    }

    private static function deleteAction(string $name, string $label, \Closure $question): Action
    {
        return Action::make($name)
            ->label($label)
            ->icon(Heroicon::OutlinedTrash)
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (QuestionSimilarity $record) => $record->status === SimilarityStatus::Open)
            ->modalDescription(function (QuestionSimilarity $record) use ($question) {
                $q = $question($record);
                $asked = app(QuestionUsage::class)->timesAsked($q->id);

                return 'Deletes "'.$q->vraag.'" and its answers. The deletion is kept in the audit log.'
                    .($asked > 0 ? " Warning: this question was asked {$asked} times in past games." : '');
            })
            ->action(function (QuestionSimilarity $record) use ($question) {
                try {
                    app(QuestionService::class)->delete($question($record));
                    Notification::make()->success()->title('Question deleted.')->send();
                } catch (ValidationException $e) {
                    Notification::make()->danger()->title('Not deleted')->body(collect($e->errors())->flatten()->first())->persistent()->send();
                }
            });
    }

    private static function search(Builder $query, string $search): Builder
    {
        $id = ltrim($search, '#');

        if (ctype_digit($id)) {
            return $query->where(fn (Builder $q) => $q->where('question_id', $id)->orWhere('similar_question_id', $id));
        }

        return $query->where(fn (Builder $q) => $q
            ->whereHas('question', fn (Builder $q) => $q->where('vraag', 'like', "%{$search}%"))
            ->orWhereHas('similarQuestion', fn (Builder $q) => $q->where('vraag', 'like', "%{$search}%")));
    }

    private static function questionCell(Question $question): string
    {
        return sprintf(
            '<div><span class="text-gray-400">#%d</span> %s</div><div class="text-sm text-gray-500">✓ %s · %s · difficulty %d · asked %dx</div>',
            $question->id,
            e($question->vraag),
            e(implode(' / ', $question->correctAnswerTexts()) ?: 'no correct answer'),
            e($question->category?->name ?? 'no category'),
            $question->difficulty,
            app(QuestionUsage::class)->timesAsked($question->id),
        );
    }

    private static function explain(QuestionSimilarity $record): HtmlString
    {
        $s = $record->signals;
        $parts = ['wording '.round(($s['text'] ?? 0) * 100).'%'];

        if (isset($s['correct_answer'])) {
            $parts[] = $s['correct_answer'] >= 0.9 ? 'same answer' : ($s['correct_answer'] < 0.3 ? 'different answer' : 'similar answer');
        }
        if (! empty($s['number_mismatch'])) {
            $parts[] = '<span class="text-danger-600">different numbers</span>';
        }
        if (! empty($s['negation_mismatch'])) {
            $parts[] = '<span class="text-danger-600">one is negated</span>';
        }
        if (! empty($s['shared_words'])) {
            $parts[] = 'shared: '.e(implode(', ', array_slice($s['shared_words'], 0, 6)));
        }

        return new HtmlString(implode('<br>', $parts));
    }
}
