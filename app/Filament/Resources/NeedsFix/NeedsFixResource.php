<?php

namespace App\Filament\Resources\NeedsFix;

use App\Filament\Resources\NeedsFix\Pages\ListNeedsFix;
use App\Models\QuestionReview;
use Filament\Resources\Resource;

/** Kept only so the old /admin/needs-fix URL redirects to the Denied tab of the Review page. */
class NeedsFixResource extends Resource
{
    protected static ?string $model = QuestionReview::class;

    protected static ?string $slug = 'needs-fix';

    protected static bool $shouldRegisterNavigation = false;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNeedsFix::route('/'),
        ];
    }
}
