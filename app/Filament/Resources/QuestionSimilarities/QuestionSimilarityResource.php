<?php

namespace App\Filament\Resources\QuestionSimilarities;

use Illuminate\Support\Facades\Cache;
use App\Enums\SimilarityStatus;
use App\Filament\Resources\QuestionSimilarities\Pages\CompareQuestions;
use App\Filament\Resources\QuestionSimilarities\Pages\ListQuestionSimilarities;
use App\Filament\Resources\QuestionSimilarities\Tables\QuestionSimilaritiesTable;
use App\Models\QuestionSimilarity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class QuestionSimilarityResource extends Resource
{
    protected static ?string $model = QuestionSimilarity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static string|UnitEnum|null $navigationGroup = 'Work queue';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Duplicates';

    protected static ?string $modelLabel = 'similar question pair';

    public static function getNavigationBadge(): ?string
    {
        $open = Cache::remember("nav-badge.similar-open", 60, fn () => QuestionSimilarity::where('status', SimilarityStatus::Open)->count());

        return $open > 0 ? (string) $open : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return QuestionSimilaritiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuestionSimilarities::route('/'),
            'compare' => CompareQuestions::route('/compare'),
        ];
    }
}
