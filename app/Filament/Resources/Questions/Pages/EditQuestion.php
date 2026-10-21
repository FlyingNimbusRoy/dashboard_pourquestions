<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Enums\ReviewOutcome;
use App\Enums\SimilarityStatus;
use App\Filament\Pages\Review;
use App\Filament\Resources\Games\GameResource;
use App\Filament\Resources\Questions\QuestionResource;
use App\Filament\Resources\Questions\Support\ServiceDelete;
use App\Models\Question;
use App\Models\QuestionReview;
use App\Models\QuestionSimilarity;
use App\Services\Crud\QuestionService;
use App\Services\Insights\QuestionUsage;
use App\Services\Validation\ReviewService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class EditQuestion extends EditRecord
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('revalidate')
                ->label('Save and re-validate')
                ->icon(Heroicon::OutlinedCheck)
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('Saves your changes and marks the question as correct again. The validation date is renewed and it leaves the denied bin.')
                ->action(function (ReviewService $reviews) {
                    $denial = $this->openDenial();
                    $this->save(shouldRedirect: false, shouldSendSavedNotification: false);

                    if ($denial !== null) {
                        $reviews->resolve($denial);
                    }

                    Notification::make()->success()->title('Question saved and re-validated.')->send();
                    $this->redirect(Review::getUrl(['tab' => 'denied']));
                })
                ->visible(fn () => $this->openDenial() !== null),
            DeleteAction::make()
                ->using(ServiceDelete::single(QuestionService::class))
                ->modalDescription(function (Question $record): string {
                    $usage = app(QuestionUsage::class);
                    $asked = $usage->timesAsked($record->id);

                    return $asked > 0
                        ? "Warning: this question was asked {$asked} times in past games (last in game #{$usage->lastGameId($record->id)}). Deleting it keeps those games intact, but they will reference a question that no longer exists. The deletion is kept in the audit log."
                        : 'Are you sure you would like to do this?';
                }),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->schema([
                Group::make([
                    Callout::make(fn () => 'Denied: '.($this->openDenial()?->reason?->getLabel() ?? 'no reason given'))
                        ->description(fn () => ($this->openDenial()?->notes ?: 'No note.').' Fix the question, then use "Save and re-validate".')
                        ->danger()
                        ->visible(fn () => $this->openDenial() !== null),
                    $this->getFormContentComponent(),
                    $this->getRelationManagersContentComponent(),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make('Usage and health')
                        ->schema([
                            View::make('filament.questions.side-panel')->viewData(fn () => $this->sidePanelData()),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]),
        ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['answers'] = $this->getRecord()->answers()->orderBy('id')->get(['id', 'answer', 'is_correct'])->toArray();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(QuestionService::class)->update($record, $data);
    }

    private function openDenial(): ?QuestionReview
    {
        return QuestionReview::query()
            ->where('question_id', $this->getRecord()->getKey())
            ->where('outcome', ReviewOutcome::NeedsFix)
            ->whereNull('resolved_at')
            ->latest('id')
            ->first();
    }

    /** @return array<string, mixed> */
    private function sidePanelData(): array
    {
        /** @var Question $question */
        $question = $this->getRecord();
        $usage = app(QuestionUsage::class);
        $gameId = $usage->lastGameId($question->id);

        return [
            'question' => $question,
            'timesAsked' => $usage->timesAsked($question->id),
            'lastAskedAt' => $usage->lastAskedAt($question->id),
            'lastGameId' => $gameId,
            'lastGameUrl' => $gameId && GameResource::hasPage('view') ? GameResource::getUrl('view', ['record' => $gameId]) : null,
            'openDuplicates' => QuestionSimilarity::query()
                ->where('status', SimilarityStatus::Open)
                ->where(fn ($q) => $q->where('question_id', $question->id)->orWhere('similar_question_id', $question->id))
                ->count(),
            'openDenials' => $this->openDenial() !== null ? 1 : 0,
            'lastChange' => $question->changes()->first(),
        ];
    }
}
