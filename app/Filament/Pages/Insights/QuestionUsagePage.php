<?php

namespace App\Filament\Pages\Insights;

use App\Filament\Resources\Questions\QuestionResource;
use App\Models\Question;
use App\Services\Insights\QuestionUsage;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

class QuestionUsagePage extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.insights.question-usage';

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Question usage';

    protected static ?string $title = 'Question usage';

    protected static ?string $slug = 'insights/question-usage';

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        $u = app(QuestionUsage::class);
        $top = collect($u->top((int) config('dashboard.insights.top_questions')));
        $questions = Question::with(['category', 'gamepack'])->whereIn('id', $top->pluck('id'))->get()->keyBy('id');

        return [
            'total' => $u->totalQuestions(),
            'asked' => $u->distinctAsked(),
            'never' => count($u->neverAskedIds()),
            'asks' => $u->totalAsks(),
            'orphans' => $u->orphanIds(),
            'top' => $top->map(fn ($r) => $r + ['q' => $questions[$r['id']] ?? null])->all(),
            'histogram' => $u->histogram(),
            'categories' => $u->perCategory(),
            'gamepacks' => $u->perGamepack(),
            'neverUrl' => QuestionResource::getUrl('index', ['activeTab' => 'never_asked']),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Never asked')
            ->description('Questions that appear in no game yet.')
            ->query(fn () => Question::query()->with(['category', 'gamepack'])->whereIn('id', app(QuestionUsage::class)->neverAskedIds()))
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('vraag')->label('Question')->searchable()->limit(80),
                TextColumn::make('category.name')->label('Category')->placeholder('-'),
                TextColumn::make('gamepack.name')->label('Gamepack')->placeholder('-'),
                TextColumn::make('difficulty')->sortable(),
            ])
            ->recordUrl(fn (Question $r) => QuestionResource::getUrl('edit', ['record' => $r]))
            ->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(10);
    }
}
