<?php

namespace App\Filament\Resources\WorkQueue\Pages;

use App\Filament\Resources\WorkQueue\SupportMessageResource;
use App\Models\InternalNote;
use App\Models\SupportMessage;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ViewSupportMessage extends ViewRecord
{
    protected static string $resource = SupportMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addNote')
                ->label('Add note')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->color('gray')
                ->form([
                    Textarea::make('note')
                        ->label('Note')
                        ->required()
                        ->minLength(3)
                        ->rows(3),
                ])
                ->action(function (SupportMessage $record, array $data) {
                    InternalNote::create([
                        'note_type'  => 'support_message',
                        'note_id'    => $record->id,
                        'note'       => $data['note'],
                        'created_by' => auth()->id(),
                    ]);
                    Notification::make()->success()->title('Note saved.')->send();
                    $this->refreshFormData([]);
                }),
            Action::make('assign')
                ->label('Assign to me')
                ->icon(\Filament\Support\Icons\Heroicon::OutlinedUser)
                ->color('info')
                ->visible(fn (SupportMessage $record) => $record->status === 'new')
                ->action(function (SupportMessage $record) {
                    $record->update([
                        'status'      => 'in_progress',
                        'assignee_id' => auth()->id(),
                    ]);
                    Notification::make()->success()->title('Assigned to you.')->send();
                    $this->refreshFormData(['status', 'assignee_id']);
                }),

            Action::make('resolve')
                ->label('Resolve')
                ->icon(\Filament\Support\Icons\Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (SupportMessage $record) => ! in_array($record->status, ['resolved']))
                ->form([
                    Textarea::make('staff_note')
                        ->label('Resolution note (optional)')
                        ->rows(3),
                ])
                ->action(function (SupportMessage $record, array $data) {
                    $record->update([
                        'status'     => 'resolved',
                        'staff_note' => $data['staff_note'] ?: $record->staff_note,
                    ]);
                    Notification::make()->success()->title('Marked as resolved.')->send();
                    $this->refreshFormData(['status', 'staff_note']);
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        /** @var SupportMessage $msg */
        $msg = $this->getRecord();
        $notes = InternalNote::where('note_type', 'support_message')
            ->where('note_id', $msg->id)
            ->with('author')
            ->orderBy('created_at')
            ->get();

        return $schema->components([
            Section::make()->columns(2)->schema([
                \Filament\Infolists\Components\TextEntry::make('type')->badge(),
                \Filament\Infolists\Components\TextEntry::make('status')->badge(),
                \Filament\Infolists\Components\TextEntry::make('player.name')->label('Player')->placeholder('Anonymous'),
                \Filament\Infolists\Components\TextEntry::make('game_id')->label('Game #')->placeholder('—'),
                \Filament\Infolists\Components\TextEntry::make('device_id')->placeholder('—'),
                \Filament\Infolists\Components\TextEntry::make('assignee.name')->label('Assignee')->placeholder('Unassigned'),
                \Filament\Infolists\Components\TextEntry::make('created_at')->label('Received')->dateTime(),
                \Filament\Infolists\Components\TextEntry::make('updated_at')->label('Last updated')->dateTime(),
            ]),
            Section::make('Message')->schema([
                \Filament\Infolists\Components\TextEntry::make('message')->hiddenLabel()->prose(),
            ]),
            Section::make('Staff note')
                ->visible(fn (SupportMessage $record) => filled($record->staff_note))
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('staff_note')->hiddenLabel()->prose(),
                ]),
            Section::make('Internal notes')->schema([
                View::make('filament.internal-notes')->viewData(['notes' => $notes]),
            ]),
        ]);
    }
}
