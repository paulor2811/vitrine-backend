<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->stateless()
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(): JsonResponse|RedirectResponse
    {
        $socialUser = Socialite::driver('google')->stateless()->user();
        $result     = $this->userService->loginWithGoogle($socialUser);

        $frontendUrl = config('app.frontend_url');
        $token       = urlencode($result['token']);

        return redirect("{$frontendUrl}?token={$token}");
    }
}
