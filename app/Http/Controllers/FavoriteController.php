<?php

namespace App\Http\Controllers;

use App\DTOs\FavoriteDTO;
use App\Models\User;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreFavoriteRequest;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct(
        private readonly FavoriteService $favoriteService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user      = $request->user();
        $favorites = $this->favoriteService->list($user->id);

        return response()->json(['success' => true, 'data' => $favorites, 'message' => 'OK']);
    }

    public function store(StoreFavoriteRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->favoriteService->add(new FavoriteDTO($user->id, $request->product_id));

        return response()->json(['success' => true, 'message' => 'Adicionado aos favoritos.'], 201);
    }

    public function destroy(Request $request, string $productId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->favoriteService->remove(new FavoriteDTO($user->id, $productId));

        return response()->json(['success' => true, 'message' => 'Removido dos favoritos.']);
    }
}
