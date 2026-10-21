<?php

namespace App\Filament\Widgets;

use App\Services\Insights\MonthlyQuota;
use Filament\Widgets\Widget;

class MonthlyQuotaWidget extends Widget
{
    protected static string $view = 'filament.widgets.monthly-quota';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected function getViewData(): array
    {
        $data = app(MonthlyQuota::class)->get();

        $paceLabel = match ($data['pace']) {
            'achieved'       => 'Target reached',
            'ahead'          => 'Ahead of pace',
            'on_track'       => 'On track',
            'slightly_behind' => 'Slightly behind',
            default          => 'Behind pace',
        };

        $paceColor = match ($data['pace']) {
            'achieved', 'ahead' => '#10b981',
            'on_track'          => '#3b82f6',
            'slightly_behind'   => '#f59e0b',
            default             => '#ef4444',
        };

        return compact('data', 'paceLabel', 'paceColor');
    }
}
