<?php

namespace App\Providers;

use App\OAuth\CustomAccessTokenRepository;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Bridge\AccessTokenRepository;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Substitui o repositório padrão do Passport para injetar is_admin no JWT
        $this->app->bind(AccessTokenRepository::class, CustomAccessTokenRepository::class);
    }

    public function boot(): void
    {
        Passport::tokensExpireIn(now()->addMinutes(15));
        Passport::personalAccessTokensExpireIn(now()->addMinutes(15));
    }
}
