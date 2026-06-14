<?php

namespace App\Services;

use App\Models\Niche;
use App\Repositories\NicheRepository;
use Illuminate\Database\Eloquent\Collection;

class NicheService
{
    public function __construct(
        private readonly NicheRepository $nicheRepository,
    ) {}

    public function listActive(): Collection
    {
        return $this->nicheRepository->allActive();
    }

    public function findBySlug(string $slug): ?Niche
    {
        return $this->nicheRepository->findBySlug($slug);
    }
}
