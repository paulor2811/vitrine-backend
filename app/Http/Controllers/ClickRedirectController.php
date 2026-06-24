<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ClickRedirectController extends Controller
{
    public function __construct(
        private readonly ProductService   $productService,
        private readonly AnalyticsService $analyticsService,
    ) {}

    private function shouldRecord(Request $request, string $productId): bool
    {
        $user = auth()->user();

        if ($user?->is_admin) {
            return false;
        }

        $identifier = $user ? 'user:' . $user->id : 'ip:' . $request->ip();
        $key = 'redirect_dedup:' . $identifier . ':' . $productId;

        return Cache::add($key, 1, now()->addHours(24));
    }

    public function redirect(Request $request, string $productId): RedirectResponse
    {
        $product = $this->productService->findByIdOnly($productId);

        if (! $product) {
            return redirect()->away(config('app.frontend_url'));
        }

        if (! $this->shouldRecord($request, $product->id)) {
            return redirect()->away($product->affiliate_url);
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
