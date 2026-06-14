<?php

namespace App\DTOs;

class FavoriteDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly string $productId,
    ) {}
}
