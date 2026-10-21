<?php

namespace App\Filament\Resources\ReviewBatches;

use Illuminate\Support\Facades\Cache;
use App\Enums\ReviewOutcome;
use App\Filament\Resources\ReviewBatches\Pages\ListReviewBatches;
use App\Filament\Resources\ReviewBatches\Pages\ReviewQuestions;
use App\Filament\Resources\ReviewBatches\Pages\ViewReviewBatch;
use App\Filament\Resources\ReviewBatches\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\ReviewBatches\Schemas\ReviewBatchInfolist;
use App\Filament\Resources\ReviewBatches\Tables\ReviewBatchesTable;
use App\Models\ReviewBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ReviewBatchResource extends Resource
{
    protected static ?string $model = ReviewBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static bool $shouldRegisterNavigation = false;


    protected static ?string $recordTitleAttribute = 'name';



    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('reviewer')
            ->withCount(['reviews', 'reviews as pending_count' => fn (Builder $query) => $query->where('outcome', ReviewOutcome::Pending)]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReviewBatchInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReviewBatchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ReviewsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReviewBatches::route('/'),
            'view' => ViewReviewBatch::route('/{record}'),
            'review' => ReviewQuestions::route('/{record}/review'),
        ];
    }
}
