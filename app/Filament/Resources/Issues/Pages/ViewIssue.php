<?php

namespace App\Filament\Resources\Issues\Pages;

use App\Filament\Resources\Issues\IssueResource;
use App\Models\DashIssueActivity;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ViewIssue extends ViewRecord
{
    protected static string $resource = IssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('assign')
                ->label('Assign to me')
                ->icon(Heroicon::OutlinedUserPlus)
                ->color('info')
                ->visible(fn (): bool => $this->getRecord()->status === 'open' && $this->getRecord()->assignee_id !== auth()->id())
                ->action(function (): void {
                    $r = $this->getRecord();
                    $old = $r->status;
                    $r->update(['status' => 'assigned', 'assignee_id' => auth()->id()]);
                    DashIssueActivity::create([
                        'issue_id' => $r->id,
                        'user_id' => auth()->id(),
                        'action' => 'assigned',
                        'from_value' => $old,
                        'to_value' => 'assigned',
                    ]);
                    $this->refreshFormData(['status', 'assignee_id']);
                }),

            Action::make('assignTo')
                ->label('Assign to...')
                ->icon(Heroicon::OutlinedUserGroup)
                ->color('gray')
                ->visible(fn (): bool => in_array($this->getRecord()->status, ['open', 'assigned', 'in_progress']))
                ->form([
                    Select::make('assignee_id')
                        ->label('Assign to')
                        ->options(User::where('is_admin', true)->pluck('name', 'id'))
                        ->default($this->getRecord()->assignee_id)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $r = $this->getRecord();
                    $old = $r->assignee_id;
                    $r->update(['status' => 'assigned', 'assignee_id' => $data['assignee_id']]);
                    DashIssueActivity::create([
                        'issue_id' => $r->id,
                        'user_id' => auth()->id(),
                        'action' => 'assigned',
                        'from_value' => $old ? (string) $old : null,
                        'to_value' => (string) $data['assignee_id'],
                    ]);
                    $this->refreshFormData(['status', 'assignee_id']);
                }),

            Action::make('startWork')
                ->label('Start working')
                ->icon(Heroicon::OutlinedPlay)
                ->color('primary')
                ->visible(fn (): bool => in_array($this->getRecord()->status, ['open', 'assigned']))
                ->action(function (): void {
                    $r = $this->getRecord();
                    $old = $r->status;
                    $r->update(['status' => 'in_progress', 'assignee_id' => $r->assignee_id ?? auth()->id()]);
                    DashIssueActivity::create([
                        'issue_id' => $r->id,
                        'user_id' => auth()->id(),
                        'action' => 'started',
                        'from_value' => $old,
                        'to_value' => 'in_progress',
                    ]);
                    $this->refreshFormData(['status']);
                }),

            Action::make('resolve')
                ->label('Resolve')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => in_array($this->getRecord()->status, ['open', 'assigned', 'in_progress']))
                ->action(function (): void {
                    $r = $this->getRecord();
                    $old = $r->status;
                    $r->update(['status' => 'resolved', 'resolved_at' => now()]);
                    DashIssueActivity::create([
                        'issue_id' => $r->id,
                        'user_id' => auth()->id(),
                        'action' => 'resolved',
                        'from_value' => $old,
                        'to_value' => 'resolved',
                    ]);
                    $this->refreshFormData(['status', 'resolved_at']);
                }),

            Action::make('wontFix')
                ->label("Won't fix")
                ->icon(Heroicon::OutlinedXCircle)
                ->color('gray')
                ->requiresConfirmation()
                ->visible(fn (): bool => ! in_array($this->getRecord()->status, ['resolved', 'wont_fix']))
                ->action(function (): void {
                    $r = $this->getRecord();
                    $old = $r->status;
                    $r->update(['status' => 'wont_fix']);
                    DashIssueActivity::create([
                        'issue_id' => $r->id,
                        'user_id' => auth()->id(),
                        'action' => 'wont_fix',
                        'from_value' => $old,
                        'to_value' => 'wont_fix',
                    ]);
                    $this->refreshFormData(['status']);
                }),
        ];
    }

    public function content(Schema $schema): Schema
    {
        $record = $this->getRecord();

        return $schema->components([
            Section::make('Issue detail')
                ->columns(2)
                ->schema([
                    View::make('filament.issues.detail')
                        ->viewData(['record' => $record]),
                ])
                ->columnSpanFull(),
            Section::make('Activity log')
                ->schema([
                    View::make('filament.issues.activity-log')
                        ->viewData(['activities' => $record->activity()->with('user')->get()]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
