<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly AuthService $authService,
    ) {}

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->stateless()
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        $socialUser = Socialite::driver('google')->stateless()->user();
        $user       = $this->userService->findOrCreateFromGoogle($socialUser);
        $tokens     = $this->authService->issueTokens($user);

        [$accessCookie, $refreshCookie, $sessionCookie] = $this->authService->buildCookies(
            $tokens['access_token'],
            $tokens['refresh_token'],
        );

        $frontendUrl = config('app.frontend_url');

        return redirect("{$frontendUrl}?token_issued=1")
            ->withCookie($accessCookie)
            ->withCookie($refreshCookie)
            ->withCookie($sessionCookie);
    }
}
