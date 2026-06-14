<?php

namespace App\Repositories;

use App\Models\Niche;
use Illuminate\Database\Eloquent\Collection;

class NicheRepository
{
    public function allActive(): Collection
    {
        return Niche::where('active', true)->orderBy('name')->get();
    }

    public function findBySlug(string $slug): ?Niche
    {
        return Niche::where('slug', $slug)->where('active', true)->first();
    }
}
