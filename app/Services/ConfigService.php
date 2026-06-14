<?php

namespace App\Services;

class ConfigService
{
    public static function appName(): string
    {
        return config('app.name');
    }

    public static function appEnv(): string
    {
        return config('app.env');
    }

    public static function appUrl(): string
    {
        return config('app.url');
    }

    public static function frontendUrl(): string
    {
        return config('app.frontend_url');
    }

    public static function isProduction(): bool
    {
        return config('app.env') === 'production';
    }

    public static function dbConnection(): string
    {
        return config('database.default');
    }
}
