<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Players\PlayerResource;
use App\Filament\Resources\WorkQueue\SupportMessageResource;
use App\Models\DashGameSession;
use App\Models\Game;
use App\Models\InternalNote;
use App\Models\SupportMessage;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Url;
use UnitEnum;

/**
 * Support tool: find a game by its ID or PIN and see its session summary, linked support messages,
 * and internal notes. Read-only. PINs repeat over time; a PIN search returns up to 10 recent games.
 */
class GameLookup extends Page
{
    protected string $view = 'filament.pages.game-lookup';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static string|UnitEnum|null $navigationGroup = 'Work queue';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Game lookup';

    #[Url]
    public string $query = '';

    /** @return array<string, mixed> */
    public function getViewData(): array
    {
        $q = trim($this->query);

        if (blank($q)) {
            return ['mode' => 'empty'];
        }

        if (ctype_digit($q)) {
            return $this->byGameId((int) $q);
        }

        return $this->byPin(strtoupper($q));
    }

    /** @return array<string, mixed> */
    private function byGameId(int $id): array
    {
        $game    = Game::with('host')->find($id);
        $session = DashGameSession::where('game_id', $id)->first();
        $support = SupportMessage::where('game_id', $id)->orderByDesc('created_at')->get();
        $notes   = InternalNote::where('note_type', 'game')->where('note_id', $id)
            ->with('author')->orderBy('created_at')->get();

        return compact('game', 'session', 'support', 'notes') + ['mode' => 'game_id'];
    }

    /** @return array<string, mixed> */
    private function byPin(string $pin): array
    {
        $sessions = DashGameSession::where('pin', $pin)
            ->orderByDesc('started_at')
            ->take(10)
            ->get();

        return ['mode' => 'pin', 'pin' => $pin, 'sessions' => $sessions];
    }

    public function addNote(int $gameId, string $note): void
    {
        InternalNote::create([
            'note_type'  => 'game',
            'note_id'    => $gameId,
            'note'       => $note,
            'created_by' => auth()->id(),
        ]);
    }
}
