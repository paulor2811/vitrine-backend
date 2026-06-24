<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use App\Services\ClickDedupService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClickRedirectController extends Controller
{
    public function __construct(
        private readonly ProductService   $productService,
        private readonly AnalyticsService $analyticsService,
        private readonly ClickDedupService $dedupService,
    ) {}

    public function redirect(Request $request, string $productId): RedirectResponse
    {
        $product = $this->productService->findByIdOnly($productId);

        if (! $product) {
            return redirect()->away(config('app.frontend_url'));
        }

        if ($this->dedupService->isUnique($request, $product->id)) {
            $this->analyticsService->recordRedirect(
                productId: $product->id,
                nicheId:   $product->niche_id,
                storeId:   $product->store_id,
                request:   $request,
            );
        }

        return redirect()->away($product->affiliate_url);
    }
}
