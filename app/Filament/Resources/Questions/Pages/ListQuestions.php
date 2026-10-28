<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\QuestionResource;
use App\Models\Question;
use App\Services\Crud\QuestionService;
use App\Services\Insights\QuestionUsage;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadTemplate')
                ->label('CSV Template')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->action(fn () => $this->downloadCsvTemplate()),
            Action::make('importCsv')
                ->label('Import CSV')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->schema([
                    FileUpload::make('csv')
                        ->label('CSV file (download template for the expected format)')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                        ->disk('local')
                        ->directory('tmp/imports')
                        ->required(),
                ])
                ->action(fn (array $data) => $this->importCsv($data['csv'])),
            CreateAction::make(),
        ];
    }

    public function downloadCsvTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $f = fopen('php://output', 'w');
            fputcsv($f, [
                'vraag', 'trivia', 'difficulty', 'is_random', 'is_nsfw',
                'category_id', 'gamepack_id',
                'answer1', 'answer2', 'answer3', 'answer4',
                'is_correct1', 'is_correct2', 'is_correct3', 'is_correct4',
            ]);
            fputcsv($f, [
                'What is the capital of France?', 'Paris is known as the City of Light.', 3, 0, 0, '', '',
                'Paris', 'London', 'Berlin', 'Madrid',
                1, 0, 0, 0,
            ]);
            fclose($f);
        }, 'questions_import_template.csv', ['Content-Type' => 'text/csv']);
    }

    public function importCsv(string $storedPath): void
    {
        $content = Storage::disk('local')->get($storedPath);
        Storage::disk('local')->delete($storedPath);

        if ($content === null) {
            Notification::make()->danger()->title('Could not read the uploaded file.')->send();

            return;
        }

        $lines = array_values(array_filter(
            explode("\n", str_replace(["\r\n", "\r"], "\n", trim($content)))
        ));

        if (count($lines) < 2) {
            Notification::make()->warning()->title('The CSV file contains no data rows.')->send();

            return;
        }

        $rows = array_map('str_getcsv', $lines);
        array_shift($rows); // drop header

        $service = app(QuestionService::class);
        $imported = 0;
        $errors = [];

        foreach ($rows as $i => $row) {
            // strip UTF-8 BOM that Excel sometimes writes on the first cell
            $row[0] = ltrim($row[0] ?? '', "\xEF\xBB\xBF");
            if (empty(trim($row[0]))) {
                continue;
            }

            $answers = [];
            for ($col = 7; $col <= 10; $col++) {
                if (! empty(trim($row[$col] ?? ''))) {
                    $answers[] = [
                        'answer' => trim($row[$col]),
                        'is_correct' => (bool) (int) ($row[$col + 4] ?? 0),
                    ];
                }
            }

            try {
                $service->create([
                    'vraag' => trim($row[0]),
                    'trivia' => filled($row[1] ?? '') ? trim($row[1]) : null,
                    'difficulty' => max(1, min(5, (int) ($row[2] ?: 3))),
                    'is_random' => (bool) (int) ($row[3] ?? 0),
                    'is_nsfw' => (bool) (int) ($row[4] ?? 0),
                    'category_id' => filled(trim($row[5] ?? '')) ? (int) $row[5] : null,
                    'gamepack_id' => filled(trim($row[6] ?? '')) ? (int) $row[6] : null,
                    'answers' => $answers,
                ]);
                $imported++;
            } catch (ValidationException $e) {
                $errors[] = 'Row '.($i + 2).': '.implode('; ', Arr::flatten($e->errors()));
            } catch (\Throwable $e) {
                $errors[] = 'Row '.($i + 2).': '.$e->getMessage();
            }
        }

        if ($imported > 0) {
            Notification::make()->success()->title("{$imported} question(s) imported.")->send();
        }

        if ($errors !== []) {
            $body = implode("\n", array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $body .= "\n…and ".(count($errors) - 5).' more.';
            }
            Notification::make()->warning()
                ->title(count($errors).' row(s) could not be imported.')
                ->body($body)
                ->send();
        }

        if ($imported === 0 && $errors === []) {
            Notification::make()->warning()->title('No valid rows found in the CSV.')->send();
        }
    }

    /** Status presets with live counts; each tab is a filter on the same table, rows open the editor. */
    public function getTabs(): array
    {
        $count = fn (callable $scope) => $scope(Question::query())->count();
        $neverAsked = fn () => app(QuestionUsage::class)->neverAskedIds();
        $threshold = (int) config('dashboard.insights.retirement_threshold');
        $retirementIds = fn () => app(QuestionUsage::class)->retirementCandidateIds($threshold);

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
            'retirement_candidates' => Tab::make('Retire?')
                ->badge(count($retirementIds()))
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('questions.id', $retirementIds())),
        ];
    }
}
