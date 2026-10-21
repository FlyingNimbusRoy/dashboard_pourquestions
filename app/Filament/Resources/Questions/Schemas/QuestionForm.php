<?php

namespace App\Filament\Resources\Questions\Schemas;

use App\Filament\Resources\Questions\QuestionResource;
use App\Models\Question;
use App\Services\Similarity\SimilarityDetector;
use Closure;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class QuestionForm
{
    /** @var array<string, list<array{question_id: int, score: float}>> */
    private static array $hintCache = [];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('maker_id')
                ->default(fn () => auth()->id()),
            ...self::fields(relationship: false),
        ]);
    }

    /**
     * The question + answers editor, shared with the review screen.
     *
     * @return list<\Filament\Schemas\Components\Component>
     */
    public static function fields(bool $withDuplicateHint = true, bool $relationship = true): array
    {
        return [
            Section::make('Question')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Textarea::make('vraag')
                        ->label('Question')
                        ->required()
                        ->maxLength(255)
                        ->rows(2)
                        ->live(onBlur: true)
                        ->columnSpanFull(),
                    Callout::make('Possible duplicates')
                        ->warning()
                        ->description(fn (Get $get, ?Question $record) => self::duplicateHint($get, $record))
                        ->visible(fn (Get $get, ?Question $record) => $withDuplicateHint && self::matches($get, $record) !== [])
                        ->columnSpanFull(),
                    Textarea::make('trivia')
                        ->helperText('Shown after the question has been answered.')
                        ->maxLength(255)
                        ->rows(2)
                        ->columnSpanFull(),
                    Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload(),
                    Select::make('gamepack_id')
                        ->label('Gamepack')
                        ->relationship('gamepack', 'name')
                        ->searchable()
                        ->preload(),
                    ToggleButtons::make('difficulty')
                        ->options(self::difficultyOptions())
                        ->default(3)
                        ->required()
                        ->inline(),
                    Toggle::make('is_nsfw')
                        ->label('NSFW')
                        ->inline(false),
                    Toggle::make('is_random')
                        ->label('Random (game flag)')
                        ->inline(false),
                ]),
            Section::make('Answers')
                ->columnSpanFull()
                ->schema([
                    Repeater::make('answers')
                        ->hiddenLabel()
                        ->when($relationship, fn (Repeater $repeater) => $repeater->relationship())
                        ->schema([
                            Hidden::make('id')->visible(! $relationship),
                            TextInput::make('answer')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(3),
                            Toggle::make('is_correct')
                                ->label('Correct answer')
                                ->onColor('success')
                                ->onIcon(Heroicon::Check)
                                ->offIcon(Heroicon::XMark)
                                ->inline(false),
                        ])
                        ->columns(4)
                        ->defaultItems(4)
                        ->minItems(2)
                        ->maxItems(4)
                        ->addActionLabel('Add answer')
                        ->rules([fn (): Closure => function (string $attribute, $value, Closure $fail) {
                            $count = count($value);
                            if ($count !== 2 && $count !== 4) {
                                $fail('A question must have exactly 2 or 4 answers.');
                            }
                            if (! collect($value)->contains(fn ($answer) => (bool) ($answer['is_correct'] ?? false))) {
                                $fail('Mark at least one answer as correct.');
                            }
                        }]),
                ]),
        ];
    }

    /** @return array<int, string> */
    public static function difficultyOptions(): array
    {
        $range = range((int) config('dashboard.difficulty.min'), (int) config('dashboard.difficulty.max'));

        return array_combine($range, array_map('strval', $range));
    }

    /** @return list<array{question_id: int, score: float}> */
    private static function matches(Get $get, ?Question $record): array
    {
        $text = (string) $get('vraag');
        if (mb_strlen(trim($text)) < 8) {
            return [];
        }

        $answers = collect($get('answers') ?? [])
            ->filter(fn ($answer) => filled($answer['answer'] ?? null))
            ->map(fn ($answer) => ['answer' => (string) $answer['answer'], 'is_correct' => (bool) ($answer['is_correct'] ?? false)])
            ->values()->all();

        $key = md5($text.json_encode($answers).$record?->id);

        return self::$hintCache[$key] ??= app(SimilarityDetector::class)->findSimilarTo($text, $answers, $record?->id);
    }

    private static function duplicateHint(Get $get, ?Question $record): HtmlString
    {
        $matches = self::matches($get, $record);
        $questions = Question::whereKey(array_column($matches, 'question_id'))->get()->keyBy('id');

        $items = collect($matches)
            ->filter(fn ($match) => $questions->has($match['question_id']))
            ->map(fn ($match) => sprintf(
                '<li><a href="%s" target="_blank" class="underline">#%d %s</a> <span class="opacity-70">(%d%% similar)</span></li>',
                e(QuestionResource::getUrl('edit', ['record' => $match['question_id']])),
                $match['question_id'],
                e($questions[$match['question_id']]->vraag),
                round($match['score'] * 100),
            ));

        return new HtmlString('<ul class="list-disc ps-4 space-y-1">'.$items->implode('').'</ul>');
    }
}
