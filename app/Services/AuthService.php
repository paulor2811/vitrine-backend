<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\RefreshTokenRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Cookie;

class AuthService
{
    private const ACCESS_TOKEN_MINUTES  = 15;
    private const REFRESH_TOKEN_DAYS    = 30;
    private const COOKIE_ACCESS         = 'vitrine_access_token';
    private const COOKIE_REFRESH        = 'vitrine_refresh_token';

    public function __construct(
        private readonly RefreshTokenRepository $refreshTokenRepository,
        private readonly TokenCacheService $tokenCacheService,
    ) {}

    public function issueTokens(User $user): array
    {
        $expiresAt    = now()->addMinutes(self::ACCESS_TOKEN_MINUTES);
        $tokenResult  = $user->createToken('api', [], $expiresAt);
        $accessToken  = $tokenResult->accessToken;
        $passportId   = $tokenResult->token->id;

        $refreshToken = $this->refreshTokenRepository->create(
            userId:          $user->id,
            passportTokenId: $passportId,
            expiresAt:       now()->addDays(self::REFRESH_TOKEN_DAYS),
        );

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in'    => self::ACCESS_TOKEN_MINUTES * 60,
        ];
    }

    public function buildCookies(string $accessToken, string $refreshToken): array
    {
        $isSecure = app()->isProduction();
        $domain   = config('app.cookie_domain');

        return [
            cookie(
                name:     self::COOKIE_ACCESS,
                value:    $accessToken,
                minutes:  self::ACCESS_TOKEN_MINUTES,
                path:     '/',
                domain:   $domain,
                secure:   $isSecure,
                httpOnly: true,
                sameSite: 'Lax',
            ),
            cookie(
                name:     self::COOKIE_REFRESH,
                value:    $refreshToken,
                minutes:  self::REFRESH_TOKEN_DAYS * 60 * 24,
                path:     '/',
                domain:   $domain,
                secure:   $isSecure,
                httpOnly: true,
                sameSite: 'Lax',
            ),
            // Cookie legível pelo JS: indica que há sessão ativa (não contém dados sensíveis)
            cookie(
                name:     'vitrine_session',
                value:    '1',
                minutes:  self::ACCESS_TOKEN_MINUTES,
                path:     '/',
                domain:   $domain,
                secure:   $isSecure,
                httpOnly: false,
                sameSite: 'Lax',
            ),
        ];
    }

    public function clearCookies(): array
    {
        $domain = config('app.cookie_domain');

        return [
            cookie(name: self::COOKIE_ACCESS,  value: '', minutes: -1, path: '/', domain: $domain),
            cookie(name: self::COOKIE_REFRESH, value: '', minutes: -1, path: '/', domain: $domain),
            cookie(name: 'vitrine_session',    value: '', minutes: -1, path: '/', domain: $domain),
        ];
    }

    public function logout(User $user, string $accessToken): void
    {
        $tokenHash = hash('sha256', $accessToken);

        $this->tokenCacheService->invalidate($tokenHash);

        $user->token()->revoke();

        $this->refreshTokenRepository->revokeAllForUser($user->id);
    }

    public function refresh(string $refreshTokenValue): array
    {
        $refreshToken = $this->refreshTokenRepository->findValid($refreshTokenValue);

        if (! $refreshToken) {
            abort(401, 'Refresh token inválido ou expirado.');
        }

        $user = $refreshToken->user;

        DB::table('oauth_access_tokens')
            ->where('id', $refreshToken->passport_token_id)
            ->update(['revoked' => true]);

        $this->tokenCacheService->invalidate(
            hash('sha256', $refreshToken->passport_token_id)
        );

        $this->refreshTokenRepository->revoke($refreshToken);

        return $this->issueTokens($user);
    }
}
