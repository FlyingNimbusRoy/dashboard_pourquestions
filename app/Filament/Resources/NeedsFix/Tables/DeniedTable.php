<?php

namespace App\Filament\Resources\NeedsFix\Tables;

use App\Enums\DenyReason;
use App\Enums\ReviewOutcome;
use App\Filament\Resources\Questions\QuestionResource;
use App\Filament\Resources\ReviewBatches\ReviewBatchResource;
use App\Models\QuestionReview;
use App\Services\Validation\ReviewService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/** The denied bin: denied questions that need a fix, then a re-validation (or a new batch). */
class DeniedTable
{
    public static function query(): Builder
    {
        return QuestionReview::query()
            ->where('outcome', ReviewOutcome::NeedsFix)
            ->with(['question.category', 'question.answers', 'reviewer']);
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->query(self::query())
            ->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(25)
            ->defaultSort('reviewed_at', 'asc')
            ->recordUrl(fn (QuestionReview $record) => QuestionResource::getUrl('edit', ['record' => $record->question_id]))
            ->emptyStateHeading('The denied bin is empty')
            ->emptyStateDescription('Denied questions appear here until they are fixed and re-validated.')
            ->emptyStateIcon(Heroicon::OutlinedCheckCircle)
            ->columns([
                TextColumn::make('question.vraag')
                    ->label('Question')
                    ->description(fn (QuestionReview $record) => '✓ '.(implode(' / ', $record->question?->correctAnswerTexts() ?? []) ?: 'no correct answer').' · '.($record->question?->category?->name ?? 'no category'))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('reason')
                    ->badge()
                    ->color('danger')
                    ->placeholder('Not given'),
                TextColumn::make('notes')
                    ->label('Note')
                    ->wrap()
                    ->limit(120),
                TextColumn::make('reviewer.name')
                    ->label('Denied by'),
                TextColumn::make('reviewed_at')
                    ->label('Denied')
                    ->date()
                    ->description(fn (QuestionReview $record) => $record->reviewed_at?->diffForHumans())
                    ->sortable(),
                TextColumn::make('resolved_at')
                    ->label('Resolved')
                    ->since()
                    ->placeholder('Open')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('open')
                    ->label('Status')
                    ->placeholder('All')
                    ->trueLabel('Open')
                    ->falseLabel('Resolved')
                    ->default(true)
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('resolved_at'),
                        false: fn (Builder $query) => $query->whereNotNull('resolved_at'),
                    ),
                SelectFilter::make('reason')->options(DenyReason::class),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->color('gray')
                    ->url(fn (QuestionReview $record) => QuestionResource::getUrl('edit', ['record' => $record->question_id])),
                Action::make('revalidate')
                    ->label('Re-validate')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Confirms the question is now correct. The validation date is renewed.')
                    ->action(function (QuestionReview $record, ReviewService $reviews) {
                        $reviews->resolve($record);
                        Notification::make()->success()->title('Question re-validated.')->send();
                    })
                    ->visible(fn (QuestionReview $record) => $record->resolved_at === null),
                Action::make('sendBack')
                    ->label('Send back')
                    ->tooltip('Send back to a new review batch')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('gray')
                    ->action(fn (QuestionReview $record, ReviewService $reviews) => self::sendBack($reviews, new Collection([$record])))
                    ->visible(fn (QuestionReview $record) => $record->resolved_at === null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('revalidate')
                        ->label('Re-validate')
                        ->icon(Heroicon::OutlinedCheck)
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalDescription('Confirms the selected questions are correct now. Their validation dates are renewed.')
                        ->action(function (Collection $records, ReviewService $reviews) {
                            $records->each(fn (QuestionReview $record) => $reviews->resolve($record));
                            Notification::make()->success()->title('Questions re-validated.')->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('sendBack')
                        ->label('Send back to a batch')
                        ->icon(Heroicon::OutlinedArrowUturnLeft)
                        ->action(fn (Collection $records, ReviewService $reviews) => self::sendBack($reviews, $records))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    private static function sendBack(ReviewService $reviews, Collection $records): void
    {
        $batch = $reviews->sendBack($records, auth()->user());

        $batch === null
            ? Notification::make()->warning()->title('Nothing to send back.')->send()
            : Notification::make()->success()->title('Sent back to a new batch.')
                ->actions([Action::make('open')->label('Review now')->url(ReviewBatchResource::getUrl('review', ['record' => $batch]))])
                ->send();
    }
}
