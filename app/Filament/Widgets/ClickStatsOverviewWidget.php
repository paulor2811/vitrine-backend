<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Repositories\AnalyticsRepository;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ClickStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $repo = app(AnalyticsRepository::class);

        $total7d  = $repo->clicksTotal(7);
        $total30d = $repo->clicksTotal(30);
        $top7d    = $repo->topRedirectedProducts(7, 1)->first();
        $topNiche = $repo->topRedirectedNiches(7, 1)->first();

        $chart7d = AnalyticsEvent::query()
            ->where('event_type', 'product_redirect')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->pluck(DB::raw('COUNT(*) as c'))
            ->map(fn ($v) => (int) $v)
            ->values()
            ->all();

        $frontendUrl = config('app.frontend_url');

        return [
            Stat::make('Cliques (7 dias)', number_format($total7d))
                ->description('Redirecionamentos servidor')
                ->chart($chart7d ?: [0])
                ->color('success'),

            Stat::make('Cliques (30 dias)', number_format($total30d))
                ->description(number_format($total30d / 30, 1) . ' por dia em média')
                ->color('primary'),

            Stat::make('Produto #1 (7d)', $top7d ? \Illuminate\Support\Str::limit($top7d->name, 40) : '—')
                ->description($top7d ? number_format($top7d->clicks_count) . ' cliques' : 'Sem dados ainda')
                ->url($top7d && $top7d->niche ? $frontendUrl . '/' . $top7d->niche->slug . '/' . $top7d->id : null)
                ->color('warning'),

            Stat::make('Nicho #1 (7d)', $topNiche ? $topNiche->icon . ' ' . $topNiche->name : '—')
                ->description($topNiche ? number_format($topNiche->clicks_count) . ' cliques' : 'Sem dados ainda')
                ->url($topNiche ? $frontendUrl . '/' . $topNiche->slug : null)
                ->color('info'),
        ];
    }
}
