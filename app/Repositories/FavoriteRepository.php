<?php

namespace App\Repositories;

use App\Models\UserFavorite;
use Illuminate\Database\Eloquent\Collection;

class FavoriteRepository
{
    public function allForUser(string $userId): Collection
    {
        return UserFavorite::with(['product.store', 'product.niche'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function exists(string $userId, string $productId): bool
    {
        return UserFavorite::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    public function add(string $userId, string $productId): UserFavorite
    {
        return UserFavorite::firstOrCreate(
            ['user_id' => $userId, 'product_id' => $productId],
            ['created_at' => now()],
        );
    }

    public function remove(string $userId, string $productId): bool
    {
        return (bool) UserFavorite::where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();
    }
}
