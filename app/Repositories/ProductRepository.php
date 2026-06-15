<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    public function findByNiche(string $nicheId): Collection
    {
        return Product::with('store')
            ->where('niche_id', $nicheId)
            ->where('active', true)
            ->orderByDesc('featured')
            ->orderBy('name')
            ->get();
    }

    public function findFeatured(int $limit = 20): Collection
    {
        return Product::with('store', 'niche')
            ->where('active', true)
            ->where('featured', true)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function findByNicheAndId(string $nicheId, string $id): ?Product
    {
        return Product::with('store', 'niche', 'media')
            ->where('niche_id', $nicheId)
            ->where('active', true)
            ->find($id);
    }

    public function findById(string $id): ?Product
    {
        return Product::with('store', 'niche', 'media')->find($id);
    }
}
