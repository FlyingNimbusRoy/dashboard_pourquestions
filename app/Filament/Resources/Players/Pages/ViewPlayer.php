<?php

namespace App\Filament\Resources\Players\Pages;

use App\Filament\Resources\Players\PlayerResource;
use App\Models\Gamepack;
use App\Models\InternalNote;
use App\Models\User;
use App\Services\Crud\UserService;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ViewPlayer extends ViewRecord
{
    protected static string $resource = PlayerResource::class;

    public function infolist(Schema $schema): Schema
    {
        /** @var User $player */
        $player = $this->getRecord();

        $notes = InternalNote::where('note_type', 'user')
            ->where('note_id', $player->id)
            ->with('author')
            ->orderBy('created_at')
            ->get();

        return $schema->components([
            Section::make()->columns(3)->columnSpanFull()->schema([
                TextEntry::make('name'),
                IconEntry::make('is_admin')->label('Admin')->boolean(),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('hosted_games_count')->label('Games hosted'),
                TextEntry::make('achievements_count')->label('Achievements'),
                TextEntry::make('gamestats.games_played')->label('Games played')->placeholder('-'),
                TextEntry::make('gamestats.games_won')->label('Games won')->placeholder('-'),
                TextEntry::make('gamestats.total_shots')->label('Total shots')->placeholder('-'),
                TextEntry::make('gamestats.shot_highscore')->label('Shot highscore')->placeholder('-'),
            ]),
            Section::make('Internal notes')->schema([
                View::make('filament.internal-notes')->viewData(['notes' => $notes]),
            ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

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
                ->action(function (User $record, array $data) {
                    InternalNote::create([
                        'note_type'  => 'user',
                        'note_id'    => $record->id,
                        'note'       => $data['note'],
                        'created_by' => auth()->id(),
                    ]);
                    Notification::make()->success()->title('Note saved.')->send();
                    $this->refreshFormData([]);
                }),

            Action::make('grantGamepack')
                ->label('Grant gamepack')
                ->icon(Heroicon::OutlinedGift)
                ->color('info')
                ->form([
                    Select::make('gamepack_id')
                        ->label('Gamepack')
                        ->options(fn () => Gamepack::orderBy('name')->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                    Textarea::make('reason')
                        ->label('Reason for grant')
                        ->required()
                        ->minLength(10)
                        ->rows(3),
                ])
                ->action(function (User $record, array $data) {
                    $gamepack = Gamepack::findOrFail($data['gamepack_id']);
                    $granted = app(UserService::class)->grantGamepack(
                        $record,
                        $gamepack,
                        $data['reason'],
                        auth()->id(),
                    );

                    if ($granted) {
                        Notification::make()->success()
                            ->title("Granted \"{$gamepack->name}\" to {$record->name}.")
                            ->send();
                    } else {
                        Notification::make()->warning()
                            ->title("{$record->name} already owns \"{$gamepack->name}\".")
                            ->send();
                    }
                }),
        ];
    }
}
