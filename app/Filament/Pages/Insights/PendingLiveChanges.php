<?php

namespace App\Filament\Pages\Insights;

use App\Models\LiveExportBatch;
use App\Models\PendingLiveChange;
use App\Models\User;
use App\Services\Insights\LiveChangeExporter;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class PendingLiveChanges extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.insights.pending-live-changes';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 30;

    protected static ?string $title = 'Pending live changes';

    protected static ?string $slug = 'insights/pending-live-changes';

    public static function getNavigationBadge(): ?string
    {
        $n = PendingLiveChange::open()->count();

        return $n ? (string) $n : null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')->label('Download SQL (all)')->icon('heroicon-o-arrow-down-tray')
                ->visible(fn (): bool => PendingLiveChange::open()->exists())
                ->action(fn () => $this->export(null)),
        ];
    }

    /** @param  list<int>|null  $ids */
    private function export(?array $ids): ?StreamedResponse
    {
        $exporter = app(LiveChangeExporter::class);
        $r = $exporter->build(auth()->user()?->name, $ids);
        if ($r['count'] === 0) {
            return null;
        }
        $batch = $exporter->markExported($r['ids'], auth()->id(), null, $r['sql']);

        return $this->download($batch);
    }

    private function download(LiveExportBatch $batch): StreamedResponse
    {
        return response()->streamDownload(fn () => print ($batch->sql), $batch->filename, ['Content-Type' => 'application/sql']);
    }

    public function redownload(int $batchId): StreamedResponse
    {
        return $this->download(LiveExportBatch::findOrFail($batchId));
    }

    /** @return Collection<int, LiveExportBatch> */
    public function getBatches(): Collection
    {
        return LiveExportBatch::query()->with('user')->latest('id')->limit(25)->get(['id', 'user_id', 'change_count', 'filename', 'exported_at']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => PendingLiveChange::query()->with('user'))
            ->defaultSort('id', 'desc')
            ->groups([
                Group::make('table_name')->label('Table'),
                Group::make('user_id')->label('User')->getTitleFromRecordUsing(fn (PendingLiveChange $r): string => $r->user?->name ?? 'system'),
            ])
            ->columns([
                TextColumn::make('created_at')->label('When')->dateTime('d-m H:i')->sortable(),
                TextColumn::make('table_name')->label('Table')->sortable(),
                TextColumn::make('operation')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'insert' => 'success',
                        'delete' => 'danger',
                        default => 'info',
                    }),
                TextColumn::make('record_id')->label('Row id')->sortable(),
                TextColumn::make('change')->label('Change (old -> new)')
                    ->state(fn (PendingLiveChange $r): string => $r->summary())
                    ->extraAttributes(['style' => 'white-space:pre-line'])->wrap(),
                TextColumn::make('user.name')->label('By')->placeholder('system'),
                TextColumn::make('status')->badge()
                    ->state(fn (PendingLiveChange $r): string => $r->discarded_at ? 'Discarded' : ($r->exported_at ? 'Exported' : 'Waiting'))
                    ->color(fn (string $state): string => match ($state) {
                        'Waiting' => 'warning',
                        'Exported' => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->options(['open' => 'Waiting', 'exported' => 'Exported', 'discarded' => 'Discarded'])->default('open')
                    ->query(fn (Builder $q, array $data) => match ($data['value'] ?? null) {
                        'open' => $q->whereNull('exported_at')->whereNull('discarded_at'),
                        'exported' => $q->whereNotNull('exported_at'),
                        'discarded' => $q->whereNotNull('discarded_at'),
                        default => $q,
                    }),
                SelectFilter::make('table_name')->label('Table')->options(fn () => PendingLiveChange::query()->distinct()->orderBy('table_name')->pluck('table_name', 'table_name')->all()),
                SelectFilter::make('user_id')->label('User')->options(fn () => User::query()->whereIn('id', PendingLiveChange::query()->select('user_id'))->pluck('name', 'id')->all()),
            ])
            ->checkIfRecordIsSelectableUsing(fn (PendingLiveChange $r): bool => $r->exported_at === null && $r->discarded_at === null)
            ->toolbarActions([
                BulkAction::make('export')->label('Download SQL for selected')->icon('heroicon-o-arrow-down-tray')
                    ->action(fn ($records) => $this->export($records->pluck('id')->all()))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('discard')->label('Discard selected (keeps local data)')->icon('heroicon-o-trash')->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('The selected changes are removed from the export. Your local data is not reverted, so it will differ from live.')
                    ->action(fn ($records) => PendingLiveChange::query()->whereIn('id', $records->pluck('id'))->whereNull('exported_at')->update(['discarded_at' => now()]))
                    ->deselectRecordsAfterCompletion(),
            ])
            ->emptyStateHeading('No pending live changes')
            ->emptyStateDescription('Every content edit made in the dashboard is queued here for the live database.');
    }
}
