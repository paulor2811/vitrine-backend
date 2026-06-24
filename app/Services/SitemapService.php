<?php

namespace App\Services;

use App\Repositories\NicheRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Cache;

class SitemapService
{
    public function __construct(
        private readonly NicheRepository   $nicheRepository,
        private readonly ProductRepository $productRepository,
    ) {}

    public function generate(): string
    {
        return Cache::remember('sitemap:xml', 3600, fn () => $this->build());
    }

    private function build(): string
    {
        $frontendUrl = rtrim(config('app.frontend_url'), '/');
        $niches      = $this->nicheRepository->allActive();
        $products    = $this->productRepository->allActiveForSitemap();

        $urls = collect();

        // Homepage
        $urls->push($this->url($frontendUrl, now()->toAtomString(), 'weekly', '1.0'));

        // Páginas de nicho
        foreach ($niches as $niche) {
            $urls->push($this->url(
                "{$frontendUrl}/{$niche->slug}",
                $niche->updated_at?->toAtomString() ?? now()->toAtomString(),
                'daily',
                '0.9'
            ));
        }

        // Páginas de produto
        foreach ($products as $product) {
            if (! $product->niche?->slug) {
                continue;
            }
            $urls->push($this->url(
                "{$frontendUrl}/{$product->niche->slug}/{$product->id}",
                $product->updated_at?->toAtomString() ?? now()->toAtomString(),
                'weekly',
                '0.7'
            ));
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            . $urls->implode('')
            . '</urlset>';
    }

    private function url(string $loc, string $lastmod, string $changefreq, string $priority): string
    {
        return "<url>"
            . "<loc>" . htmlspecialchars($loc) . "</loc>"
            . "<lastmod>{$lastmod}</lastmod>"
            . "<changefreq>{$changefreq}</changefreq>"
            . "<priority>{$priority}</priority>"
            . "</url>";
    }
}
