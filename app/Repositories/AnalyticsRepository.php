<?php

namespace App\Repositories;

use App\Models\AnalyticsEvent;
use Illuminate\Support\Facades\DB;

class AnalyticsRepository
{
    public function record(array $data): AnalyticsEvent
    {
        return AnalyticsEvent::create($data + ['created_at' => now()]);
    }

    public function claimSession(string $sessionId, string $userId): int
    {
        return AnalyticsEvent::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->update(['user_id' => $userId]);
    }

    public function topNiches(int $days = 7, int $limit = 10): array
    {
        return AnalyticsEvent::select('niche_id', DB::raw('count(*) as total'))
            ->where('event_type', 'niche_view')
            ->whereNotNull('niche_id')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('niche_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function topProducts(int $days = 7, int $limit = 10): array
    {
        return AnalyticsEvent::select('product_id', DB::raw('count(*) as total'))
            ->where('event_type', 'product_click')
            ->whereNotNull('product_id')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function clicksTotal(int $days): int
    {
        return AnalyticsEvent::where('event_type', 'product_redirect')
            ->where('created_at', '>=', now()->subDays($days))
            ->count();
    }

    public function topRedirectedProducts(int $days = 7, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Product::query()
            ->select([
                'products.id',
                'products.name',
                'products.niche_id',
                DB::raw('COUNT(analytics_events.id) as clicks_count'),
            ])
            ->join('analytics_events', 'analytics_events.product_id', '=', 'products.id')
            ->where('analytics_events.event_type', 'product_redirect')
            ->where('analytics_events.created_at', '>=', now()->subDays($days))
            ->groupBy('products.id', 'products.name', 'products.niche_id')
            ->orderByDesc('clicks_count')
            ->limit($limit)
            ->with('niche:id,name,icon,slug', 'store:id,name')
            ->get();
    }

    public function topRedirectedNiches(int $days = 7, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Niche::query()
            ->select([
                'niches.id',
                'niches.name',
                'niches.icon',
                DB::raw('COUNT(analytics_events.id) as clicks_count'),
            ])
            ->join('analytics_events', 'analytics_events.niche_id', '=', 'niches.id')
            ->where('analytics_events.event_type', 'product_redirect')
            ->where('analytics_events.created_at', '>=', now()->subDays($days))
            ->groupBy('niches.id', 'niches.name', 'niches.icon')
            ->orderByDesc('clicks_count')
            ->limit($limit)
            ->get();
    }

    public function clicksBySource(int $days = 7, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return AnalyticsEvent::query()
            ->select([
                DB::raw("COALESCE(utm_source, 'direto') as utm_source"),
                DB::raw('COUNT(*) as clicks_count'),
            ])
            ->where('event_type', 'product_redirect')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy(DB::raw("COALESCE(utm_source, 'direto')"))
            ->orderByDesc('clicks_count')
            ->limit($limit)
            ->get();
    }
}
