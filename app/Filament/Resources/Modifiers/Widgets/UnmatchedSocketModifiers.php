<?php

namespace App\Filament\Resources\Modifiers\Widgets;

use App\Filament\Resources\Modifiers\Support\SocketSettingsForm;
use App\Models\ModifierSetting;
use App\Services\Crud\ModifierService;
use App\Services\Crud\ModifierSettingService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** Socket-server modifiers that have no row in the game `modifiers` table yet. */
class UnmatchedSocketModifiers extends TableWidget
{
    protected static ?string $heading = 'Socket modifiers without a game row';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => app(ModifierSettingService::class)->query()->whereNull('modifier_id'))
            ->defaultSort('key')
            ->paginated(false)
            ->columns([
                TextColumn::make('key')->badge(),
                TextColumn::make('name')->description(fn (ModifierSetting $r) => $r->description),
                IconColumn::make('turnbased')->boolean(),
                TextColumn::make('settings.duration')->label('Turns')->placeholder('-'),
                ToggleColumn::make('is_active')->label('Active')
                    ->updateStateUsing(fn (ModifierSetting $record, bool $state) => app(ModifierSettingService::class)->update($record, ['is_active' => $state])),
            ])
            ->recordActions([
                EditAction::make()->label('Socket settings')->modalHeading('Socket settings')
                    ->schema(SocketSettingsForm::fields())
                    ->using(fn (ModifierSetting $record, array $data) => app(ModifierSettingService::class)->update($record, $data)),
                Action::make('createGameRow')->label('Create game modifier')->icon('heroicon-o-plus')
                    ->requiresConfirmation()
                    ->modalDescription('Creates a row in the game modifiers table (queued as a pending live change) and links it to this socket key.')
                    ->action(function (ModifierSetting $record): void {
                        $mod = app(ModifierService::class)->create([
                            'name' => $record->name,
                            'description' => $record->description,
                            'turnbased' => $record->turnbased,
                            'is_active' => $record->is_active,
                            'coupled_gamepack_id' => $record->gamepack_id,
                        ]);
                        app(ModifierSettingService::class)->update($record, ['modifier_id' => $mod->id]);
                        Notification::make()->success()->title('Game modifier created')->send();
                    }),
            ])
            ->emptyStateHeading('Every socket modifier has a game row');
    }
}
