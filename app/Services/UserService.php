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

    public function register(RegisterUserDTO $dto): User
    {
        return $this->userRepository->create([
            'name'     => $dto->name,
            'email'    => $dto->email,
            'password' => $dto->password,
        ]);
    }

    public function login(LoginUserDTO $dto): User
    {
        if (! Auth::attempt(['email' => $dto->email, 'password' => $dto->password])) {
            throw new AuthenticationException('Credenciais inválidas.');
        }

        /** @var User $user */
        return Auth::user();
    }

    public function findOrCreateFromGoogle(SocialiteUser $socialUser): User
    {
        return $this->userRepository->updateOrCreateFromGoogle([
            'google_id'         => $socialUser->getId(),
            'name'              => $socialUser->getName(),
            'email'             => $socialUser->getEmail(),
            'avatar_url'        => $socialUser->getAvatar(),
            'email_verified_at' => now(),
        ]);
    }

    public function claimSession(User $user, string $sessionId): void
    {
        $this->analyticsService->claimSession($sessionId, $user->id);
    }
}
