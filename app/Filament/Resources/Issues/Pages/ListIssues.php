<?php

namespace App\Filament\Resources\Issues\Pages;

use App\Filament\Resources\Issues\IssueResource;
use App\Models\DashIssue;
use App\Models\DashIssueActivity;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListIssues extends ListRecords
{
    protected static string $resource = IssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New issue')
                ->icon(Heroicon::OutlinedPlus)
                ->modalHeading('Report an issue')
                ->modalWidth(Width::Large)
                ->schema([
                    Select::make('type')
                        ->options([
                            'content' => 'Content',
                            'balance' => 'Balance',
                            'tech' => 'Tech',
                        ])
                        ->default('content')
                        ->required(),
                    Select::make('subject_type')
                        ->label('Subject')
                        ->options([
                            'question' => 'Question',
                            'category' => 'Category',
                            'minigame' => 'Minigame',
                            'modifier' => 'Modifier',
                            'game' => 'Game',
                            'general' => 'General',
                        ])
                        ->default('question')
                        ->live()
                        ->required(),
                    TextInput::make('subject_id')
                        ->label('Subject ID')
                        ->numeric()
                        ->minValue(1)
                        ->hidden(fn ($get): bool => $get('subject_type') === 'general'),
                    Textarea::make('reason_summary')
                        ->label('What is wrong?')
                        ->required()
                        ->minLength(3)
                        ->rows(3),
                    Select::make('assignee_id')
                        ->label('Assign to')
                        ->options(fn () => User::where('is_admin', true)->pluck('name', 'id'))
                        ->placeholder('Unassigned'),
                ])
                ->mutateDataUsing(fn (array $data): array => [
                    ...$data,
                    'subject_id' => $data['subject_type'] === 'general' ? null : ($data['subject_id'] ?? null),
                    'reason_code' => 'manual',
                    'status' => filled($data['assignee_id'] ?? null) ? 'assigned' : 'open',
                    'created_by' => auth()->id(),
                ])
                ->after(function (DashIssue $record): void {
                    DashIssueActivity::create([
                        'issue_id' => $record->id,
                        'user_id' => auth()->id(),
                        'action' => 'created',
                        'to_value' => $record->status,
                    ]);
                }),
        ];
    }
}
