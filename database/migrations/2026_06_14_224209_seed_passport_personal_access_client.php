<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('oauth_clients')
            ->where('personal_access_client', true)
            ->where('revoked', false)
            ->exists();

        if ($exists) {
            return;
        }

        $clientId = (string) Str::uuid();

        DB::table('oauth_clients')->insert([
            'id'                     => $clientId,
            'user_id'                => null,
            'name'                   => 'Personal Access Client',
            'secret'                 => null,
            'provider'               => 'users',
            'redirect'               => 'http://localhost',
            'personal_access_client' => true,
            'password_client'        => false,
            'revoked'                => false,
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        DB::table('oauth_personal_access_clients')->insert([
            'client_id'  => $clientId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('oauth_clients')
            ->where('personal_access_client', true)
            ->where('name', 'Personal Access Client')
            ->delete();
    }
};
