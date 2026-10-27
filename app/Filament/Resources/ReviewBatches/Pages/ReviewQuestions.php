<?php

namespace App\Filament\Resources\ReviewBatches\Pages;

use App\Enums\DenyReason;
use App\Enums\ReviewOutcome;
use App\Enums\SimilarityStatus;
use App\Filament\Pages\Review;
use App\Filament\Resources\QuestionSimilarities\QuestionSimilarityResource;
use App\Filament\Resources\Questions\QuestionResource;
use App\Filament\Resources\ReviewBatches\ReviewBatchResource;
use App\Models\Question;
use App\Models\QuestionReview;
use App\Models\QuestionSimilarity;
use App\Models\ReviewBatch;
use App\Services\Validation\ReviewBatchPlanner;
use App\Services\Validation\ReviewService;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

/**
 * Works through a review batch one question at a time: Approve or Deny (with a reason), then the
 * next question appears without a page load. Keyboard: A approve, D deny, S skip, 1-5 difficulty.
 * When nothing is pending any more the page shows the batch summary.
 */
class ReviewQuestions extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ReviewBatchResource::class;

    #[Locked]
    public ?int $reviewId = null;

    /** The difficulty (1-5) the reviewer has chosen for the current question. */
    public int $difficulty = 3;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->loadNext();
    }

    public function getTitle(): string|Htmlable
    {
        return $this->getBatch()->name;
    }

    public function getSubheading(): ?string
    {
        [$done, $total] = $this->progress();

        return $this->reviewId === null
            ? "Batch finished · {$total} questions"
            : sprintf('%d / %d', min($done + 1, $total), $total);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components($this->reviewId === null ? $this->summaryComponents() : $this->questionComponents());
    }

    /** @return list<\Filament\Schemas\Components\Component> */
    private function questionComponents(): array
    {
        $question = $this->getQuestion();

        return [
            $this->similarityCallout($question),
            Section::make()
                ->schema([
                    View::make('filament.review.question')->viewData([
                        'question' => $question,
                        'done' => $this->progress()[0],
                        'total' => $this->progress()[1],
                    ]),
                ])
                ->footer([
                    Actions::make([
                        Action::make('approve')
                            ->label('Approve')
                            ->icon(Heroicon::OutlinedCheck)
                            ->color('success')
                            ->size('xl')
                            ->keyBindings(['a'])
                            ->action(fn () => $this->approve()),
                        $this->denyAction(),
                        Action::make('skip')
                            ->label('Skip')
                            ->color('gray')
                            ->keyBindings(['s'])
                            ->action(fn () => $this->skip()),
                        Action::make('edit')
                            ->label('Edit question')
                            ->icon(Heroicon::OutlinedPencilSquare)
                            ->color('gray')
                            ->outlined()
                            ->url(QuestionResource::getUrl('edit', ['record' => $question->id]), shouldOpenInNewTab: true),
                    ])->key("reviewActions"),
                ]),
            Section::make('Difficulty')
                ->description('1 = almost everyone knows this, 5 = only experts. Applied when you approve.')
                ->compact()
                ->schema([
                    Actions::make(array_map(
                        fn (int $level) => Action::make("difficulty{$level}")
                            ->label((string) $level)
                            ->color(fn () => $this->difficulty === $level ? 'primary' : 'gray')
                            ->outlined(fn () => $this->difficulty !== $level)
                            ->keyBindings([(string) $level])
                            ->action(fn () => $this->difficulty = $level),
                        range(config('dashboard.difficulty.min'), config('dashboard.difficulty.max')),
                    )),
                ]),
        ];
    }

    private function denyAction(): Action
    {
        return Action::make('deny')
            ->label('Deny')
            ->icon(Heroicon::OutlinedXMark)
            ->color('danger')
            ->size('xl')
            ->keyBindings(['d'])
            ->modalHeading('Deny this question')
            ->modalDescription('It goes to the denied bin, where it can be fixed and re-validated.')
            ->modalWidth(Width::Small)
            ->modalSubmitActionLabel('Deny')
            ->schema([
                Radio::make('reason')
                    ->options(DenyReason::class)
                    ->required(),
                Textarea::make('note')
                    ->label('Note (optional)')
                    ->rows(2),
            ])
            ->action(fn (array $data) => $this->deny($data['reason'], $data['note'] ?? null));
    }

    /** @return list<\Filament\Schemas\Components\Component> */
    private function summaryComponents(): array
    {
        $counts = $this->getBatch()->reviews()->selectRaw('outcome, count(*) as total')->groupBy('outcome')->pluck('total', 'outcome');
        $count = fn (ReviewOutcome $outcome) => (int) ($counts[$outcome->value] ?? 0);

        return [
            Section::make('Batch finished')
                ->description(sprintf(
                    '%d approved, %d denied, %d skipped.',
                    $count(ReviewOutcome::Approved),
                    $count(ReviewOutcome::NeedsFix),
                    $count(ReviewOutcome::Skipped),
                ))
                ->footer([
                    Actions::make([
                        Action::make('next')
                            ->label('Start next batch')
                            ->icon(Heroicon::OutlinedPlay)
                            ->action(function (ReviewBatchPlanner $planner) {
                                $batch = $planner->startBatch(auth()->user());

                                if ($batch === null) {
                                    Notification::make()->info()->title('No more questions are due for review.')->send();

                                    return;
                                }

                                $this->redirect(ReviewBatchResource::getUrl('review', ['record' => $batch]));
                            }),
                        Action::make('denied')
                            ->label('Open the denied bin')
                            ->color('gray')
                            ->url(Review::getUrl(['tab' => 'denied'])),
                        Action::make('back')
                            ->label('Back to Review')
                            ->color('gray')
                            ->url(Review::getUrl()),
                    ]),
                ]),
        ];
    }

    protected function approve(): void
    {
        app(ReviewService::class)->approve($this->getReview(), auth()->user(), $this->difficulty);
        $this->advance('Approved.');
    }

    protected function deny(DenyReason $reason, ?string $note): void
    {
        try {
            app(ReviewService::class)->deny($this->getReview(), auth()->user(), $reason, $note, $this->difficulty);
        } catch (ValidationException $e) {
            Notification::make()->danger()->title('Not saved')->body(collect($e->errors())->flatten()->first())->send();

            return;
        }

        $this->advance('Denied: '.$reason->getLabel().'.');
    }

    protected function skip(): void
    {
        app(ReviewService::class)->skip($this->getReview(), auth()->user());
        $this->advance('Skipped.');
    }

    private function advance(string $message): void
    {
        Notification::make()->success()->title($message)->duration(1500)->send();
        Cache::forget('nav-badge.review.'.auth()->id());
        Cache::forget('nav-badge.denied-open');
        $this->loadNext();
    }

    private function loadNext(): void
    {
        $review = QuestionReview::query()
            ->where('review_batch_id', $this->getBatch()->getKey())
            ->where('outcome', ReviewOutcome::Pending->value)
            ->orderBy('id')
            ->first();

        $this->reviewId = $review?->id;

        // The page content (and the question in it) was already built earlier in this request,
        // when Livewire looked up the clicked action. Drop both so the next question renders.
        $this->questionCache = null;
        unset($this->cachedSchemas['content']);

        if ($review !== null) {
            $this->difficulty = (int) $review->difficulty_before;
        }
    }

    /** @return array{0: int, 1: int} */
    private function progress(): array
    {
        $batch = $this->getBatch();

        return [
            $batch->reviews()->where('outcome', '!=', ReviewOutcome::Pending)->count(),
            $batch->reviews()->count(),
        ];
    }

    private function similarityCallout(Question $question): Callout
    {
        $open = QuestionSimilarity::query()
            ->where('status', SimilarityStatus::Open)
            ->where(fn ($q) => $q->where('question_id', $question->id)->orWhere('similar_question_id', $question->id))
            ->count();

        return Callout::make($open === 1 ? '1 open duplicate suspect' : "{$open} open duplicate suspects")
            ->description('This question may have a duplicate. Check it under "Duplicates".')
            ->warning()
            ->actions([
                Action::make('similar')
                    ->label('Show')
                    ->url(QuestionSimilarityResource::getUrl('index', ['tableSearch' => (string) $question->id]))
                    ->openUrlInNewTab(),
            ])
            ->visible($open > 0);
    }

    private function getBatch(): ReviewBatch
    {
        /** @var ReviewBatch */
        return $this->getRecord();
    }

    private function getReview(): QuestionReview
    {
        return QuestionReview::where('review_batch_id', $this->getBatch()->id)->findOrFail($this->reviewId);
    }

    private ?Question $questionCache = null;

    private function getQuestion(): Question
    {
        return $this->questionCache ??= $this->getReview()->question()->with(['answers', 'category', 'gamepack'])->firstOrFail();
    }
}
