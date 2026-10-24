<?php

namespace App\Filament\Resources\Modifiers\Pages;

use App\Filament\Resources\Modifiers\ModifierResource;
use App\Filament\Resources\Modifiers\Support\SocketSettingsForm;
use App\Filament\Resources\Questions\Support\ServiceDelete;
use App\Models\Modifier;
use App\Services\Analytics\ModifierStats;
use App\Services\Crud\ModifierService;
use App\Services\Crud\ModifierSettingService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditModifier extends EditRecord
{
    protected static string $resource = ModifierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(ServiceDelete::single(ModifierService::class)),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $setting = $this->getRecord()->socketSetting;
        if ($setting) {
            $data['socketSetting'] = [
                'is_active'  => $setting->is_active,
                'gamepack_id' => $setting->gamepack_id,
                'settings'   => $setting->settings ?? [],
            ];
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $socketData = Arr::pull($data, 'socketSetting');
        $record = app(ModifierService::class)->update($record, $data);

        if ($socketData !== null && $record->socketSetting) {
            $socketData['settings'] = array_filter($socketData['settings'] ?? [], fn ($v) => $v !== null);
            app(ModifierSettingService::class)->update($record->socketSetting, $socketData);
        }

        return $record;
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
                            View::make('filament.modifiers.side-panel')
                                ->viewData(fn () => $this->modifierSidePanelData()),
                        ]),
                    Section::make('Socket settings')
                        ->description('Controls how the socket server uses this modifier.')
                        ->visible(fn () => (bool) $this->getRecord()->socketSetting)
                        ->schema(SocketSettingsForm::fields('socketSetting.')),
                ])->columnSpan(['lg' => 1]),
            ]),
        ]);
    }

    /** @return array<string, mixed> */
    private function modifierSidePanelData(): array
    {
        /** @var Modifier $modifier */
        $modifier = $this->getRecord();
        $stats = app(ModifierStats::class);

        return [
            'appearances' => $stats->appearanceCount($modifier->name),
            'openIssues'  => $stats->openIssueCount($modifier->id),
        ];
    }
}
