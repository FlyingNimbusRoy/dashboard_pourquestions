<?php

namespace App\Filament\Resources\QuestionSimilarities\Pages;

use App\Enums\SimilarityStatus;
use App\Filament\Resources\QuestionSimilarities\QuestionSimilarityResource;
use App\Filament\Resources\Questions\QuestionResource;
use App\Models\Question;
use App\Models\QuestionSimilarity;
use App\Services\Crud\QuestionService;
use App\Services\Insights\QuestionUsage;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

/**
 * Works through open duplicate pairs one at a time. After each decision the next pair loads
 * without a page navigation. When the open queue is empty, a summary screen appears.
 */
class CompareQuestions extends Page
{
    protected static string $resource = QuestionSimilarityResource::class;

    protected static string $view = 'filament.question-similarities.compare';

    protected static bool $shouldRegisterNavigation = false;

    #[Locked]
    public ?int $pairId = null;

    public function mount(): void
    {
        $this->loadNext();
    }

    public function getTitle(): string|Htmlable
    {
        return 'Compare duplicates';
    }

    public function getSubheading(): ?string
    {
        if ($this->pairId === null) {
            return 'All open pairs reviewed.';
        }

        $remaining = QuestionSimilarity::where('status', SimilarityStatus::Open)->count();

        return number_format($remaining).' open pair'.($remaining === 1 ? '' : 's').' remaining';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to list')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(QuestionSimilarityResource::getUrl('index')),
        ];
    }

    public function dismiss(): void
    {
        $pair = $this->getPair();
        $pair->update(['status' => SimilarityStatus::Dismissed, 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        $this->advance('Dismissed — not a duplicate.');
    }

    public function resolve(): void
    {
        $pair = $this->getPair();
        $pair->update(['status' => SimilarityStatus::Resolved, 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        $this->advance('Marked as resolved.');
    }

    public function deleteQuestion(int $questionId): void
    {
        $question = Question::findOrFail($questionId);
        try {
            app(QuestionService::class)->delete($question);
        } catch (ValidationException $e) {
            Notification::make()->danger()->title('Not deleted')->body(collect($e->errors())->flatten()->first())->persistent()->send();

            return;
        }

        $pair = QuestionSimilarity::find($this->pairId);
        if ($pair) {
            $pair->update(['status' => SimilarityStatus::Resolved, 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        }

        $this->advance("Deleted #{$questionId} and resolved the pair.");
    }

    private function advance(string $message): void
    {
        Notification::make()->success()->title($message)->duration(1500)->send();
        $this->loadNext();
    }

    private function loadNext(): void
    {
        $pair = QuestionSimilarity::where('status', SimilarityStatus::Open)
            ->whereHas('question')
            ->whereHas('similarQuestion')
            ->orderByDesc('score')
            ->orderBy('id')
            ->first();

        $this->pairId = $pair?->id;
    }

    public function getPair(): QuestionSimilarity
    {
        return QuestionSimilarity::with([
            'question.answers', 'question.category',
            'similarQuestion.answers', 'similarQuestion.category',
        ])->findOrFail($this->pairId);
    }

}
