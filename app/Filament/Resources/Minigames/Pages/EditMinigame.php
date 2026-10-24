<?php

namespace App\Filament\Resources\Minigames\Pages;

use App\Filament\Resources\Minigames\MinigameResource;
use App\Filament\Resources\Questions\Support\ServiceDelete;
use App\Models\Minigame;
use App\Services\Analytics\MinigameStats;
use App\Services\Crud\MinigameService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class EditMinigame extends EditRecord
{
    protected static string $resource = MinigameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(ServiceDelete::single(MinigameService::class)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(MinigameService::class)->update($record, $data);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->schema([
                Group::make([
                    $this->getFormContentComponent(),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make('Stats')
                        ->schema([
                            View::make('filament.minigames.side-panel')
                                ->viewData(fn () => $this->minigameSidePanelData()),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]),
        ]);
    }

    /** @return array<string, mixed> */
    private function minigameSidePanelData(): array
    {
        /** @var Minigame $minigame */
        $minigame = $this->getRecord();
        $stats = app(MinigameStats::class);

        return [
            'appearances'  => $stats->appearanceCount($minigame->key),
            'openIssues'   => $stats->openIssueCount($minigame->id),
        ];
    }
}
