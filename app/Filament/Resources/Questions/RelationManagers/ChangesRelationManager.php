<?php

namespace App\Filament\Resources\Questions\RelationManagers;

use App\Models\QuestionChange;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Read-only audit trail of everything changed on this question through the dashboard. */
class ChangesRelationManager extends RelationManager
{
    protected static string $relationship = 'changes';

    protected static ?string $title = 'History';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime(),
                TextColumn::make('user.name')
                    ->label('By')
                    ->placeholder('System'),
                TextColumn::make('action')
                    ->badge(),
                TextColumn::make('changes')
                    ->label('Details')
                    ->state(fn (QuestionChange $record) => self::describe($record))
                    ->wrap(),
            ]);
    }

    private static function describe(QuestionChange $change): string
    {
        return collect($change->changes)
            ->map(function ($value, string $field) {
                if (is_array($value) && array_key_exists('new', $value)) {
                    $old = $value['old_label'] ?? $value['old'];
                    $new = $value['new_label'] ?? $value['new'];

                    return $field.': '.json_encode($old, JSON_UNESCAPED_UNICODE).' → '.json_encode($new, JSON_UNESCAPED_UNICODE);
                }

                return $field.': '.json_encode($value, JSON_UNESCAPED_UNICODE);
            })
            ->implode('; ');
    }
}
