<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\NicheRepository;
use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly NicheRepository   $nicheRepository,
    ) {}

    public function listByNiche(string $nicheSlug): Collection
    {
        $niche = $this->nicheRepository->findBySlug($nicheSlug);

        if (! $niche) {
            throw new NotFoundHttpException('Nicho não encontrado.');
        }

        return $this->productRepository->findByNiche($niche->id);
    }

    public function listFeatured(int $limit = 20): Collection
    {
        return $this->productRepository->findFeatured($limit);
    }

    public function findById(string $nicheSlug, string $productId): Product
    {
        $niche = $this->nicheRepository->findBySlug($nicheSlug);

        if (! $niche) {
            throw new NotFoundHttpException('Nicho não encontrado.');
        }

        $product = $this->productRepository->findByNicheAndId($niche->id, $productId);

        if (! $product) {
            throw new NotFoundHttpException('Produto não encontrado.');
        }

        return $product;
    }
}
