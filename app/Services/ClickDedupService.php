<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Decides whether a product redirect click should be recorded.
 *
 * Rules (evaluated in order):
 *   1. Admin users  → never record (prevents test clicks from polluting stats)
 *   2. Logged-in    → 1 click per product per 24 h, keyed by user ID
 *   3. Anonymous    → 1 click per product per 24 h, keyed by IP address
 */
class ClickDedupService
{
    private const TTL_HOURS = 24;

    private const KEY_PREFIX = 'click_dedup';

    public function isUnique(Request $request, string $productId): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user?->is_admin) {
            return false;
        }

        return Cache::add(
            key:   $this->cacheKey($request, $productId, $user),
            value: 1,
            ttl:   now()->addHours(self::TTL_HOURS),
        );
    }

    private function cacheKey(Request $request, string $productId, ?User $user): string
    {
        $identifier = $user
            ? 'user:' . $user->id
            : 'ip:' . $request->ip();

        return self::KEY_PREFIX . ':' . $identifier . ':' . $productId;
    }
}
