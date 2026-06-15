<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\TokenCacheService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateFromCookie
{
    public function __construct(
        private readonly TokenCacheService $tokenCacheService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tokenValue = $request->cookie('vitrine_access_token');

        if (! $tokenValue) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $userId = $this->resolveUserId($tokenValue);

        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $user = User::find($userId);

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        Auth::setUser($user);

        return $next($request);
    }

    private function resolveUserId(string $tokenValue): ?string
    {
        $tokenHash = hash('sha256', $tokenValue);

        $cached = $this->tokenCacheService->get($tokenHash);

        if ($cached !== null) {
            return $cached;
        }

        // Cache miss: decodifica o JWT para obter jti e exp sem verificar assinatura
        $payload = $this->decodeJwtPayload($tokenValue);

        if (! $payload) {
            return null;
        }

        $jti = $payload['jti'] ?? null;
        $sub = $payload['sub'] ?? null;
        $exp = $payload['exp'] ?? 0;

        if (! $jti || ! $sub || $exp < time()) {
            return null;
        }

        // Verifica no banco se não foi revogado
        $dbToken = DB::table('oauth_access_tokens')
            ->where('id', $jti)
            ->first();

        if (! $dbToken || $dbToken->revoked) {
            return null;
        }

        $ttl = $exp - time();
        $this->tokenCacheService->set($tokenHash, (string) $sub, $ttl);

        return (string) $sub;
    }

    private function decodeJwtPayload(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        $payload = base64_decode(
            str_pad(strtr($parts[1], '-_', '+/'), strlen($parts[1]) % 4, '=', STR_PAD_RIGHT)
        );

        if (! $payload) {
            return null;
        }

        return json_decode($payload, true) ?? null;
    }
}
