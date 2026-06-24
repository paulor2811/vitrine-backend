<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClickRedirectController extends Controller
{
    public function __construct(
        private readonly ProductService   $productService,
        private readonly AnalyticsService $analyticsService,
    ) {}

    public function redirect(Request $request, string $productId): RedirectResponse
    {
        $product = $this->productService->findByIdOnly($productId);

        if (! $product) {
            return redirect()->away(config('app.frontend_url'));
        }

        $this->analyticsService->recordRedirect(
            productId: $product->id,
            nicheId:   $product->niche_id,
            storeId:   $product->store_id,
            request:   $request,
        );

        return redirect()->away($product->affiliate_url);
    }
}
