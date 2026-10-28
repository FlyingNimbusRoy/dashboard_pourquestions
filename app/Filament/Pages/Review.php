<?php

namespace App\Filament\Pages;

use App\Enums\ReviewOutcome;
use App\Filament\Resources\NeedsFix\Tables\DeniedTable;
use App\Filament\Resources\ReviewBatches\ReviewBatchResource;
use App\Filament\Resources\ReviewBatches\Tables\ReviewBatchesTable;
use App\Filament\Resources\ReviewBatches\Widgets\ReviewThroughput;
use App\Models\Category;
use App\Models\QuestionReview;
use App\Models\ReviewBatch;
use App\Models\User;
use App\Services\Validation\ReviewBatchPlanner;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Url;
use UnitEnum;

/** The work queue for validation: my batches, the denied bin and all batches in one place. */
class Review extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Work queue';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'review';

    protected static ?string $title = 'Review';

    #[Url(as: 'tab')]
    public string $activeTab = 'mine';

    public static function getNavigationBadge(): ?string
    {
        $count = Cache::remember('nav-badge.review.'.auth()->id(), 60, fn () => self::deniedOpenCount() + self::myPendingCount());

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Denied questions waiting for a fix plus your own pending review items';
    }

    public static function deniedOpenCount(): int
    {
        return QuestionReview::where('outcome', ReviewOutcome::NeedsFix)->whereNull('resolved_at')->count();
    }

    public static function myPendingCount(): int
    {
        return QuestionReview::where('outcome', ReviewOutcome::Pending)
            ->whereHas('batch', fn (Builder $q) => $q->open()->where('reviewer_id', auth()->id()))
            ->count();
    }

    public function updatedActiveTab(): void
    {
        $this->tableSort = null;
        $this->tableSearch = '';
        $this->tableFilters = null;
        $this->deselectAllTableRecords();
        $this->resetTable();
    }

    public function getSubheading(): ?string
    {
        return 'Small batches, one question at a time. Denied questions wait in the Denied tab until they are fixed.';
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->startAction(),
            Action::make('custom')
                ->label('Custom batch')
                ->icon(Heroicon::OutlinedPlus)
                ->color('gray')
                ->modalDescription('Takes the questions that have gone longest without review. Questions already waiting in a batch or denied are skipped.')
                ->schema([
                    TextInput::make('size')
                        ->numeric()->minValue(1)->maxValue(200)
                        ->default(fn () => config('dashboard.validation.batch_size'))
                        ->required(),
                    Select::make('category_id')
                        ->label('Only category')
                        ->options(fn () => Category::orderBy('name')->pluck('name', 'id'))
                        ->placeholder('All categories')
                        ->searchable(),
                    Select::make('reviewer_id')
                        ->label('Reviewer')
                        ->options(fn () => User::reviewers()->orderBy('name')->pluck('name', 'id'))
                        ->default(fn () => auth()->id())
                        ->placeholder('Reviewer with the fewest open batches'),
                ])
                ->action(function (array $data, ReviewBatchPlanner $planner) {
                    $batch = $planner->createBatch(
                        reviewer: filled($data['reviewer_id']) ? User::find($data['reviewer_id']) : null,
                        size: (int) $data['size'],
                        categoryId: filled($data['category_id']) ? (int) $data['category_id'] : null,
                    );

                    $batch === null
                        ? Notification::make()->info()->title('No questions are due for review.')->send()
                        : $this->redirect(ReviewBatchResource::getUrl('review', ['record' => $batch]));
                }),
        ];
    }

    private function startAction(): Action
    {
        return Action::make('start')
            ->label('Start a batch')
            ->icon(Heroicon::OutlinedPlay)
            ->action(function (ReviewBatchPlanner $planner) {
                $batch = $planner->startBatch(auth()->user());

                if ($batch === null) {
                    Notification::make()->info()->title('No questions are due for review.')->send();

                    return;
                }

                $this->redirect(ReviewBatchResource::getUrl('review', ['record' => $batch]));
            });
    }

    protected function getHeaderWidgets(): array
    {
        return [ReviewThroughput::class];
    }

    public function content(Schema $schema): Schema
    {
        $denied = self::deniedOpenCount();

        return $schema->components([
            Tabs::make('reviewTabs')
                ->livewireProperty('activeTab')
                ->tabPanels(false)
                ->contained(false)
                ->tabs([
                    'mine' => Tab::make('My batches'),
                    'denied' => Tab::make('Denied')->badge($denied > 0 ? $denied : null)->badgeColor('danger'),
                    'all' => Tab::make('All batches'),
                ]),
            EmbeddedTable::make(),
        ]);
    }

    public function table(Table $table): Table
    {
        if ($this->activeTab === 'denied') {
            return DeniedTable::configure($table);
        }

        $table = ReviewBatchesTable::configure($table)
            ->query(ReviewBatchResource::getEloquentQuery())
            ->recordUrl(fn (ReviewBatch $record) => ReviewBatchResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading($this->activeTab === 'mine' ? 'No batches assigned to you' : 'No batches yet')
            ->emptyStateDescription('Start a batch to get the questions that have gone longest without review.')
            ->emptyStateIcon(Heroicon::OutlinedClipboardDocumentCheck)
            ->emptyStateActions([$this->startAction()]);

        return $this->activeTab === 'mine'
            ? $table->modifyQueryUsing(fn (Builder $query) => $query->where('reviewer_id', auth()->id()))
            : $table;
    }
}
