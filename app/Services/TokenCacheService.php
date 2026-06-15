<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class TokenCacheService
{
    private const PREFIX = 'token_cache:';

    public function get(string $tokenHash): ?string
    {
        $value = Redis::get(self::PREFIX . $tokenHash);
        return $value ? (string) $value : null;
    }

    public function set(string $tokenHash, string $userId, int $ttlSeconds): void
    {
        if ($ttlSeconds <= 0) {
            return;
        }

        Redis::setex(self::PREFIX . $tokenHash, $ttlSeconds, $userId);
    }

    public function invalidate(string $tokenHash): void
    {
        Redis::del(self::PREFIX . $tokenHash);
    }
}
