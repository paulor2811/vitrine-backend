<?php

namespace App\Repositories;

use App\Models\RefreshToken;
use Illuminate\Support\Str;

class RefreshTokenRepository
{
    public function create(string $userId, string $passportTokenId, \DateTimeInterface $expiresAt): string
    {
        $value = Str::random(64);

        RefreshToken::create([
            'id'                => Str::uuid(),
            'user_id'           => $userId,
            'passport_token_id' => $passportTokenId,
            'token_hash'        => hash('sha256', $value),
            'expires_at'        => $expiresAt,
        ]);

        return $value;
    }

    public function findValid(string $tokenValue): ?RefreshToken
    {
        return RefreshToken::where('token_hash', hash('sha256', $tokenValue))
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function revoke(RefreshToken $token): void
    {
        $token->update(['revoked' => true]);
    }

    public function revokeAllForUser(string $userId): void
    {
        RefreshToken::where('user_id', $userId)
            ->where('revoked', false)
            ->update(['revoked' => true]);
    }
}
