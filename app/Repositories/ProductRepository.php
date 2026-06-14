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

    public function findById(string $id): ?Product
    {
        return Product::with('store', 'niche')->find($id);
    }
}
