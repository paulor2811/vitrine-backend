<?php

namespace App\Http\Controllers;

use App\Services\NicheService;
use Illuminate\Http\JsonResponse;

class NicheController extends Controller
{
    public function __construct(
        private readonly NicheService $nicheService,
    ) {}

    public function index(): JsonResponse
    {
        $niches = $this->nicheService->listActive();

        return response()->json([
            'success' => true,
            'data'    => $niches,
            'message' => 'OK',
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $niche = $this->nicheService->findBySlug($slug);

        if (! $niche) {
            return response()->json(['success' => false, 'message' => 'Nicho não encontrado.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $niche,
            'message' => 'OK',
        ]);
    }
}
