<?php

namespace App\Services;

use App\DTOs\FavoriteDTO;
use App\Repositories\FavoriteRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class FavoriteService
{
    public function __construct(
        private readonly FavoriteRepository $favoriteRepository,
    ) {}

    public function list(string $userId): Collection
    {
        return $this->favoriteRepository->allForUser($userId);
    }

    public function add(FavoriteDTO $dto): void
    {
        $this->favoriteRepository->add($dto->userId, $dto->productId);
    }

    public function remove(FavoriteDTO $dto): void
    {
        $removed = $this->favoriteRepository->remove($dto->userId, $dto->productId);

        if (! $removed) {
            throw ValidationException::withMessages(['product_id' => 'Favorito não encontrado.']);
        }
    }
}
