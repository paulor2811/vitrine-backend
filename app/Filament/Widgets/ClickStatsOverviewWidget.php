<?php

namespace App\Filament\Widgets;

use App\Repositories\AnalyticsRepository;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClickStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $repo = app(AnalyticsRepository::class);

        $total7d  = $repo->clicksTotal(7);
        $total30d = $repo->clicksTotal(30);

        $top7d = $repo->topRedirectedProducts(7, 1)->first();

        return [
            Stat::make('Cliques (7 dias)', number_format($total7d))
                ->description('Redirecionamentos registrados')
                ->color('success'),

            Stat::make('Cliques (30 dias)', number_format($total30d))
                ->description('Redirecionamentos registrados')
                ->color('primary'),

            Stat::make('Produto #1 (7 dias)', $top7d?->name ?? '—')
                ->description($top7d ? number_format($top7d->clicks_count) . ' cliques' : 'Sem dados ainda')
                ->color('warning'),
        ];
    }
}
