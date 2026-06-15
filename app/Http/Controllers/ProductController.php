<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    public function byNiche(string $nicheSlug): JsonResponse
    {
        try {
            $products = $this->productService->listByNiche($nicheSlug);
        } catch (NotFoundHttpException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $products,
            'message' => 'OK',
        ]);
    }

    public function featured(): JsonResponse
    {
        $products = $this->productService->listFeatured();

        return response()->json([
            'success' => true,
            'data'    => $products,
            'message' => 'OK',
        ]);
    }

    public function show(string $nicheSlug, string $productId): JsonResponse
    {
        try {
            $product = $this->productService->findById($nicheSlug, $productId);
        } catch (NotFoundHttpException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $product,
            'message' => 'OK',
        ]);
    }
}
