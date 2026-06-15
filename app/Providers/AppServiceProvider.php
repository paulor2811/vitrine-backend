<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Passport::tokensExpireIn(now()->addMinutes(15));
        Passport::personalAccessTokensExpireIn(now()->addMinutes(15));
    }
}
