<?php

namespace App\Filament\Resources\Players\RelationManagers;

use App\Models\GamepackPurchase;
use App\Services\Crud\UserService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class GamepackPurchasesRelationManager extends RelationManager
{
    protected static string $relationship = 'gamepackPurchases';

    protected static ?string $title = 'Gamepack purchases';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('gamepack.name')->label('Gamepack'),
                IconColumn::make('opened')->boolean(),
                IconColumn::make('access_through_admin_grant')->label('Admin grant')->boolean(),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->recordActions([
                Action::make('revoke')
                    ->label('Revoke grant')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->visible(fn (GamepackPurchase $record) => $record->access_through_admin_grant)
                    ->requiresConfirmation()
                    ->modalHeading('Revoke admin grant')
                    ->modalDescription('This removes the pack from the player immediately. Requires a reason for the audit log.')
                    ->form([
                        Textarea::make('reason')
                            ->label('Reason')
                            ->required()
                            ->minLength(5)
                            ->rows(2),
                    ])
                    ->action(function (GamepackPurchase $record, array $data) {
                        try {
                            app(UserService::class)->revokeGamepack($record, $data['reason'], auth()->id());
                            Notification::make()->success()->title('Grant revoked.')->send();
                        } catch (ValidationException $e) {
                            Notification::make()->danger()->title('Not revoked')
                                ->body(collect($e->errors())->flatten()->first())->send();
                        }
                    }),
            ]);
    }
}
