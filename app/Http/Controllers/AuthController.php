<?php

namespace App\Http\Controllers;

use App\DTOs\LoginUserDTO;
use App\DTOs\RegisterUserDTO;
use App\Http\Requests\ClaimSessionRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\SetPasswordRequest;
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
        [$accessCookie, $refreshCookie, $sessionCookie] = $this->authService->buildCookies(
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
        ], 201)->withCookie($accessCookie)->withCookie($refreshCookie)->withCookie($sessionCookie);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user   = $this->userService->login(new LoginUserDTO(
            email:    $request->email,
            password: $request->password,
        ));
        $tokens = $this->authService->issueTokens($user);
        [$accessCookie, $refreshCookie, $sessionCookie] = $this->authService->buildCookies(
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
        ])->withCookie($accessCookie)->withCookie($refreshCookie)->withCookie($sessionCookie);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user        = $request->user();
        $accessToken = $request->cookie('vitrine_access_token');

        $this->authService->logout($user, $accessToken ?? '');

        [$accessCookie, $refreshCookie, $sessionCookie] = $this->authService->clearCookies();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado.',
        ])->withCookie($accessCookie)->withCookie($refreshCookie)->withCookie($sessionCookie);
    }

    public function refresh(Request $request): JsonResponse
    {
        $refreshTokenValue = $request->cookie('vitrine_refresh_token');

        if (! $refreshTokenValue) {
            return response()->json(['success' => false, 'message' => 'Refresh token ausente.'], 401);
        }

        $tokens = $this->authService->refresh($refreshTokenValue);
        [$accessCookie, $refreshCookie, $sessionCookie] = $this->authService->buildCookies(
            $tokens['access_token'],
            $tokens['refresh_token'],
        );

        return response()->json([
            'success' => true,
            'data'    => ['expires_in' => $tokens['expires_in']],
            'message' => 'Token renovado.',
        ])->withCookie($accessCookie)->withCookie($refreshCookie)->withCookie($sessionCookie);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data'    => array_merge($user->toArray(), [
                'has_password' => ! is_null($user->password),
            ]),
            'message' => 'OK',
        ]);
    }

    public function setPassword(SetPasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! is_null($user->password)) {
            if (! $request->filled('current_password')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Informe a senha atual.',
                ], 422);
            }
            if (! \Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Senha atual incorreta.',
                ], 422);
            }
        }

        $user->update(['password' => $request->password]);

        return response()->json([
            'success' => true,
            'message' => 'Senha definida com sucesso.',
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
