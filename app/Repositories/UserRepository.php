<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByGoogleId(string $googleId): ?User
    {
        return User::where('google_id', $googleId)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function updateOrCreateFromGoogle(array $googleData): User
    {
        return User::updateOrCreate(
            ['google_id' => $googleData['google_id']],
            $googleData,
        );
    }
}
