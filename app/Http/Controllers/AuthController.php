<?php

namespace App\Http\Controllers;

use App\DTOs\LoginUserDTO;
use App\DTOs\RegisterUserDTO;
use App\Http\Requests\ClaimSessionRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly AuthService $authService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user   = $this->userService->register(new RegisterUserDTO(
            name:     $request->name,
            email:    $request->email,
            password: $request->password,
        ));
        $tokens = $this->authService->issueTokens($user);
        [$accessCookie, $refreshCookie] = $this->authService->buildCookies(
            $tokens['access_token'],
            $tokens['refresh_token'],
        );

        return response()->json([
            'success' => true,
            'data'    => [
                'user'       => $user,
                'expires_in' => $tokens['expires_in'],
            ],
            'message' => 'Conta criada com sucesso.',
        ], 201)->withCookie($accessCookie)->withCookie($refreshCookie);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user   = $this->userService->login(new LoginUserDTO(
            email:    $request->email,
            password: $request->password,
        ));
        $tokens = $this->authService->issueTokens($user);
        [$accessCookie, $refreshCookie] = $this->authService->buildCookies(
            $tokens['access_token'],
            $tokens['refresh_token'],
        );

        return response()->json([
            'success' => true,
            'data'    => [
                'user'       => $user,
                'expires_in' => $tokens['expires_in'],
            ],
            'message' => 'Login realizado com sucesso.',
        ])->withCookie($accessCookie)->withCookie($refreshCookie);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user        = $request->user();
        $accessToken = $request->cookie('vitrine_access_token');

        $this->authService->logout($user, $accessToken ?? '');

        [$accessCookie, $refreshCookie] = $this->authService->clearCookies();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado.',
        ])->withCookie($accessCookie)->withCookie($refreshCookie);
    }

    public function refresh(Request $request): JsonResponse
    {
        $refreshTokenValue = $request->cookie('vitrine_refresh_token');

        if (! $refreshTokenValue) {
            return response()->json(['success' => false, 'message' => 'Refresh token ausente.'], 401);
        }

        $tokens = $this->authService->refresh($refreshTokenValue);
        [$accessCookie, $refreshCookie] = $this->authService->buildCookies(
            $tokens['access_token'],
            $tokens['refresh_token'],
        );

        return response()->json([
            'success' => true,
            'data'    => ['expires_in' => $tokens['expires_in']],
            'message' => 'Token renovado.',
        ])->withCookie($accessCookie)->withCookie($refreshCookie);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $request->user(),
            'message' => 'OK',
        ]);
    }

    public function claimSession(ClaimSessionRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->userService->claimSession($user, $request->session_id);

        return response()->json(['success' => true, 'message' => 'Sessão vinculada.']);
    }
}
