<?php

namespace App\Filament\Resources\Questions\Tables;

use App\Enums\SimilarityStatus;
use App\Filament\Resources\Questions\Schemas\QuestionForm;
use App\Filament\Resources\ReviewBatches\ReviewBatchResource;
use App\Models\Category;
use App\Models\Gamepack;
use App\Models\Question;
use App\Models\User;
use App\Filament\Resources\Questions\Support\ServiceDelete;
use App\Services\Crud\QuestionService;
use App\Services\Questions\QuestionMover;
use App\Services\Validation\ReviewBatchPlanner;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use App\Filament\Pages\Review;
use App\Filament\Resources\Questions\QuestionResource;
use App\Services\Insights\QuestionUsage;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class QuestionsTable
{
    public static function configure(Table $table, bool $showCategory = true): Table
    {
        return $table
            ->deferLoading()->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(25)
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['category', 'gamepack', 'answers']))
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('vraag')
                    ->label('Question')
                    ->searchable()
                    ->wrap()
                    ->description(fn (Question $record) => '✓ '.implode(' / ', $record->correctAnswerTexts())),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->placeholder('None')
                    ->sortable()
                    ->visible($showCategory),
                TextColumn::make('gamepack.name')
                    ->label('Gamepack')
                    ->placeholder('None')
                    ->toggleable(),
                SelectColumn::make('difficulty')
                    ->options(QuestionForm::difficultyOptions())
                    ->selectablePlaceholder(false)
                    ->rules(['required', 'integer'])
                    ->updateStateUsing(function (Question $record, $state): int {
                        app(QuestionService::class)->update($record, ['difficulty' => (int) $state]);

                        return (int) $state;
                    })
                    ->sortable(),
                TextColumn::make('asks')
                    ->label('Asks')
                    ->state(fn (Question $record) => app(QuestionUsage::class)->timesAsked($record->id))
                    ->numeric()
                    ->alignEnd()
                    ->sortable(query: function (Builder $query, string $direction) {
                        $cases = collect(app(QuestionUsage::class)->counts())->map(fn ($n, $id) => 'when '.(int) $id.' then '.(int) $n)->implode(' ');

                        return $query->orderByRaw($cases === '' ? '0' : "(case questions.id {$cases} else 0 end) ".($direction === 'desc' ? 'desc' : 'asc'));
                    }),
                TextColumn::make('last_validated')
                    ->label('Validated')
                    ->formatStateUsing(fn (Question $record) => $record->wasEverValidated() ? $record->last_validated->diffForHumans() : 'Never')
                    ->color(fn (Question $record) => $record->isValidationDue() ? 'warning' : 'success')
                    ->sortable(),
                IconColumn::make('is_nsfw')
                    ->label('NSFW')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->visible($showCategory),
                Filter::make('uncategorized')
                    ->label('Without category')
                    ->query(fn (Builder $query) => $query->whereNull('category_id'))
                    ->visible($showCategory),
                SelectFilter::make('gamepack')
                    ->relationship('gamepack', 'name')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('difficulty')
                    ->options(QuestionForm::difficultyOptions())
                    ->multiple(),
                SelectFilter::make('validation')
                    ->options(['never' => 'Never validated', 'due' => 'Due for review', 'valid' => 'Validated'])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'never' => $query->where('last_validated', '<=', Question::NEVER_VALIDATED),
                        'due' => $query->validationDue(),
                        'valid' => $query->whereNot(fn (Builder $q) => $q->validationDue()),
                        default => $query,
                    }),
                Filter::make('has_open_duplicates')
                    ->label('Has open similar questions')
                    ->query(fn (Builder $query) => $query->whereExists(fn ($sub) => $sub
                        ->from('dash_question_similarities')
                        ->where('status', SimilarityStatus::Open->value)
                        ->where(fn ($q) => $q->whereColumn('question_id', 'questions.id')->orWhereColumn('similar_question_id', 'questions.id')))),
                TernaryFilter::make('is_nsfw')
                    ->label('NSFW'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('validate')
                    ->label('Mark validated')
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Confirms this question is still correct and relevant. Sets the validation date to today.')
                    ->action(function (Question $record) {
                        app(QuestionService::class)->validate($record);
                        Notification::make()->success()->title('Marked as validated.')->send();
                    })
                    ->visible(fn (Question $record) => $record->isValidationDue()),
                ActionGroup::make([
                    Action::make('moveCategory')
                        ->label('Move to category')
                        ->icon(Heroicon::OutlinedTag)
                        ->fillForm(fn (Question $record) => ['category_id' => $record->category_id])
                        ->schema([
                            Select::make('category_id')
                                ->label('Category')
                                ->options(fn () => Category::orderBy('name')->pluck('name', 'id'))
                                ->placeholder('No category')
                                ->searchable(),
                        ])
                        ->action(function (Question $record, array $data, QuestionMover $mover) {
                            $mover->moveToCategory([$record], filled($data['category_id']) ? (int) $data['category_id'] : null);
                            Notification::make()->success()->title('Category updated.')->send();
                        }),
                    Action::make('moveGamepack')
                        ->label('Move to gamepack')
                        ->icon(Heroicon::OutlinedRectangleStack)
                        ->fillForm(fn (Question $record) => ['gamepack_id' => $record->gamepack_id])
                        ->schema([
                            Select::make('gamepack_id')
                                ->label('Gamepack')
                                ->options(fn () => Gamepack::orderBy('name')->pluck('name', 'id'))
                                ->placeholder('No gamepack')
                                ->searchable(),
                        ])
                        ->action(function (Question $record, array $data, QuestionMover $mover) {
                            $mover->moveToGamepack([$record], filled($data['gamepack_id']) ? (int) $data['gamepack_id'] : null);
                            Notification::make()->success()->title('Gamepack updated.')->send();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('validate')
                        ->label('Mark validated')
                        ->icon(Heroicon::OutlinedShieldCheck)
                        ->requiresConfirmation()
                        ->modalDescription('Sets the validation date to today for all selected questions.')
                        ->action(function (Collection $records) {
                            $service = app(QuestionService::class);
                            $records->each(fn (Question $q) => $service->validate($q));
                            Notification::make()->success()->title("Validated {$records->count()} question(s).")->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('moveCategory')
                        ->label('Move to category')
                        ->icon(Heroicon::OutlinedTag)
                        ->schema([
                            Select::make('category_id')
                                ->label('Category')
                                ->options(fn () => Category::orderBy('name')->pluck('name', 'id'))
                                ->placeholder('No category')
                                ->searchable(),
                        ])
                        ->action(function (Collection $records, array $data, QuestionMover $mover) {
                            $moved = $mover->moveToCategory($records, filled($data['category_id']) ? (int) $data['category_id'] : null);
                            Notification::make()->success()->title("Moved {$moved} questions.")->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('moveGamepack')
                        ->label('Move to gamepack')
                        ->icon(Heroicon::OutlinedRectangleStack)
                        ->schema([
                            Select::make('gamepack_id')
                                ->label('Gamepack')
                                ->options(fn () => Gamepack::orderBy('name')->pluck('name', 'id'))
                                ->placeholder('No gamepack')
                                ->searchable(),
                        ])
                        ->action(function (Collection $records, array $data, QuestionMover $mover) {
                            $moved = $mover->moveToGamepack($records, filled($data['gamepack_id']) ? (int) $data['gamepack_id'] : null);
                            Notification::make()->success()->title("Moved {$moved} questions.")->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('setDifficulty')
                        ->label('Set difficulty')
                        ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                        ->schema([
                            ToggleButtons::make('difficulty')
                                ->options(QuestionForm::difficultyOptions())
                                ->required()
                                ->inline(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(fn (Question $question) => app(QuestionService::class)->update($question, ['difficulty' => (int) $data['difficulty']]));
                            Notification::make()->success()->title('Difficulty updated.')->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('review')
                        ->label('Send to review')
                        ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                        ->modalDescription('Creates a review batch with the selected questions. Questions already waiting for review are skipped.')
                        ->schema([
                            TextInput::make('name')
                                ->default('Manual review '.now()->format('Y-m-d'))
                                ->required(),
                            Select::make('reviewer_id')
                                ->label('Reviewer')
                                ->options(fn () => User::reviewers()->orderBy('name')->pluck('name', 'id'))
                                ->placeholder('Any reviewer'),
                        ])
                        ->action(function (Collection $records, array $data, ReviewBatchPlanner $planner) {
                            $batch = $planner->createBatch(
                                reviewer: filled($data['reviewer_id']) ? User::find($data['reviewer_id']) : null,
                                questionIds: $records->modelKeys(),
                                name: $data['name'],
                            );

                            $batch === null
                                ? Notification::make()->warning()->title('All selected questions are already waiting for review.')->send()
                                : Notification::make()->success()->title('Review batch created.')
                                    ->actions([Action::make('open')->url(ReviewBatchResource::getUrl('view', ['record' => $batch]))])
                                    ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()
                        ->using(ServiceDelete::bulk(QuestionService::class))
                        ->modalDescription(function (Collection $records): string {
                            $counts = app(QuestionUsage::class)->counts();
                            $asked = $records->filter(fn (Question $question) => isset($counts[$question->id]));
                            $total = $asked->sum(fn (Question $question) => $counts[$question->id]);

                            return $asked->isNotEmpty()
                                ? "Warning: {$asked->count()} of the {$records->count()} selected questions were asked in past games (asked {$total} times in total). Deleting them keeps those games intact, but they will reference questions that no longer exist. The deletions are kept in the audit log."
                                : 'Are you sure you would like to do this?';
                        }),
                ]),
            ])
            ->emptyStateHeading(fn ($livewire) => match (data_get($livewire, 'activeTab')) {
                'no_category' => 'Every question has a category',
                'no_gamepack' => 'Every question is in a gamepack',
                'never_validated' => 'Every question has been validated',
                'due' => 'Nothing is due for review',
                'never_asked' => 'Every question has been asked at least once',
                default => 'No questions found',
            })
            ->emptyStateDescription(fn ($livewire) => match (data_get($livewire, 'activeTab')) {
                'due' => 'Questions return here once their validation date is older than the review interval.',
                'never_asked', 'no_category', 'no_gamepack', 'never_validated' => 'Nothing to clean up in this view right now.',
                default => 'Adjust the search or filters, or add a new question.',
            })
            ->emptyStateIcon(Heroicon::OutlinedQuestionMarkCircle)
            ->emptyStateActions([
                Action::make('startBatch')
                    ->label('Start a review batch')
                    ->icon(Heroicon::OutlinedPlay)
                    ->url(fn () => Review::getUrl())
                    ->visible(fn ($livewire) => in_array(data_get($livewire, 'activeTab'), ['due', 'never_validated'], true)),
                Action::make('create')
                    ->label('New question')
                    ->icon(Heroicon::OutlinedPlus)
                    ->url(fn () => QuestionResource::getUrl('create'))
                    ->visible(fn ($livewire) => in_array(data_get($livewire, 'activeTab'), [null, 'all'], true)),
            ]);
    }
}
