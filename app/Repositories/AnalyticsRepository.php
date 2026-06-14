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
}
