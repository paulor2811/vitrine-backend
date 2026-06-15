<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Passport 13: personal access client is identified by grant_types JSON containing 'personal_access'
return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('oauth_clients')
            ->where('revoked', false)
            ->whereRaw("grant_types::jsonb @> '\"personal_access\"'::jsonb")
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('oauth_clients')->insert([
            'id'            => (string) Str::uuid(),
            'owner_id'      => null,
            'owner_type'    => null,
            'name'          => 'Personal Access Client',
            'secret'        => null,
            'provider'      => null,
            'redirect_uris' => json_encode([]),
            'grant_types'   => json_encode(['personal_access']),
            'revoked'       => false,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('oauth_clients')
            ->whereRaw("grant_types::jsonb @> '\"personal_access\"'::jsonb")
            ->delete();
    }
};
