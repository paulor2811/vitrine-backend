<?php

namespace App\Services;

use App\DTOs\LoginUserDTO;
use App\DTOs\RegisterUserDTO;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AnalyticsService $analyticsService,
    ) {}

    public function register(RegisterUserDTO $dto): array
    {
        $user = $this->userRepository->create([
            'name'     => $dto->name,
            'email'    => $dto->email,
            'password' => $dto->password,
        ]);

        $token = $user->createToken('api')->accessToken;

        return ['user' => $user, 'token' => $token];
    }

    public function login(LoginUserDTO $dto): array
    {
        if (! Auth::attempt(['email' => $dto->email, 'password' => $dto->password])) {
            throw new AuthenticationException('Credenciais inválidas.');
        }

        /** @var User $user */
        $user  = Auth::user();
        $token = $user->createToken('api')->accessToken;

        return ['user' => $user, 'token' => $token];
    }

    public function loginWithGoogle(SocialiteUser $socialUser): array
    {
        $user = $this->userRepository->updateOrCreateFromGoogle([
            'google_id'          => $socialUser->getId(),
            'name'               => $socialUser->getName(),
            'email'              => $socialUser->getEmail(),
            'avatar_url'         => $socialUser->getAvatar(),
            'email_verified_at'  => now(),
        ]);

        $token = $user->createToken('api')->accessToken;

        return ['user' => $user, 'token' => $token];
    }

    public function logout(User $user): void
    {
        $user->token()->revoke();
    }

    public function claimSession(User $user, string $sessionId): void
    {
        $this->analyticsService->claimSession($sessionId, $user->id);
    }
}
