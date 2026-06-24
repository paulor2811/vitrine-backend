<?php

namespace App\Filament\Widgets;

use App\Repositories\AnalyticsRepository;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClicksBySourceWidget extends BaseWidget
{
    protected static ?string $heading = '🌐 Cliques por Fonte (30 dias)';

    protected static ?int $sort = 4;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $rows = app(AnalyticsRepository::class)->clicksBySource(30, 5);

        return $rows->map(fn ($row) => Stat::make(
            ucfirst($row->utm_source),
            number_format($row->clicks_count) . ' cliques',
        )->color('warning'))->all();
    }
}
