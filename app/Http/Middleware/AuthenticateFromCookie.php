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

        // Decodifica o payload do JWT (sem verificar assinatura — só leitura de claims)
        $payload = $this->decodeJwtPayload($tokenValue);

        if (! $payload) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $jti     = $payload['jti']      ?? null;
        $sub     = $payload['sub']      ?? null;
        $exp     = $payload['exp']      ?? 0;
        $isAdmin = (bool) ($payload['is_admin'] ?? false);

        if (! $jti || ! $sub || $exp < time()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        // Usa cache para evitar consulta ao banco a cada request
        $tokenHash = hash('sha256', $tokenValue);

        if (! $this->isTokenNotRevoked($tokenHash, $jti, $exp)) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $user = User::find($sub);

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        Auth::setUser($user);

        // is_admin vem do JWT assinado — disponível para middlewares subsequentes
        $request->attributes->set('is_admin', $isAdmin);

        return $next($request);
    }

    private function isTokenNotRevoked(string $tokenHash, string $jti, int $exp): bool
    {
        // Cache hit = token já foi validado e não estava revogado
        if ($this->tokenCacheService->get($tokenHash) !== null) {
            return true;
        }

        // Cache miss: consulta o banco
        $dbToken = DB::table('oauth_access_tokens')
            ->where('id', $jti)
            ->first();

        if (! $dbToken || $dbToken->revoked) {
            return false;
        }

        $ttl = $exp - time();
        $this->tokenCacheService->set($tokenHash, $jti, $ttl);

        return true;
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
