<?php

namespace App\Filament\Resources\WorkQueue\Pages;

use App\Filament\Resources\WorkQueue\SupportMessageResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListSupportMessages extends ListRecords
{
    protected static string $resource = SupportMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New support message')
                ->icon(Heroicon::OutlinedPlus)
                ->modalHeading('Log a support message')
                ->modalWidth(Width::Large)
                ->schema([
                    Select::make('type')
                        ->options([
                            'feedback' => 'Feedback',
                            'bug'      => 'Bug report',
                            'support'  => 'Support',
                        ])
                        ->default('support')
                        ->required(),
                    Textarea::make('message')
                        ->required()
                        ->minLength(3)
                        ->rows(4),
                    // Players share the users table, so search instead of preloading everyone.
                    Select::make('user_id')
                        ->label('Player')
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search) => User::query()
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->limit(25)
                            ->pluck('name', 'id'))
                        ->getOptionLabelUsing(fn ($value): ?string => User::find($value)?->name)
                        ->placeholder('Anonymous'),
                    TextInput::make('game_id')
                        ->label('Game #')
                        ->numeric()
                        ->minValue(1),
                    Select::make('assignee_id')
                        ->label('Assign to')
                        ->options(fn () => User::where('is_admin', true)->pluck('name', 'id'))
                        ->placeholder('Unassigned'),
                ])
                ->mutateDataUsing(fn (array $data): array => [
                    ...$data,
                    'status' => filled($data['assignee_id'] ?? null) ? 'in_progress' : 'new',
                ]),
        ];
    }
}
