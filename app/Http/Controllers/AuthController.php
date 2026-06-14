<?php

namespace App\Http\Controllers;

use App\DTOs\LoginUserDTO;
use App\DTOs\RegisterUserDTO;
use App\Http\Requests\ClaimSessionRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->userService->register(new RegisterUserDTO(
            name:     $request->name,
            email:    $request->email,
            password: $request->password,
        ));

        return response()->json([
            'success' => true,
            'data'    => ['user' => $result['user'], 'token' => $result['token']],
            'message' => 'Conta criada com sucesso.',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->userService->login(new LoginUserDTO(
            email:    $request->email,
            password: $request->password,
        ));

        return response()->json([
            'success' => true,
            'data'    => ['user' => $result['user'], 'token' => $result['token']],
            'message' => 'Login realizado com sucesso.',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->userService->logout($user);

        return response()->json(['success' => true, 'message' => 'Logout realizado.']);
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
