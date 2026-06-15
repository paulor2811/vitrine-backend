<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Passport published migrations use foreignId (bigint) for user_id, but users PK is uuid.
// Tables are empty so we can safely recreate the column with the correct type.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE oauth_access_tokens DROP COLUMN IF EXISTS user_id');
        DB::statement('ALTER TABLE oauth_access_tokens ADD COLUMN user_id uuid NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS oauth_access_tokens_user_id_index ON oauth_access_tokens (user_id)');

        DB::statement('ALTER TABLE oauth_auth_codes DROP COLUMN IF EXISTS user_id');
        DB::statement('ALTER TABLE oauth_auth_codes ADD COLUMN user_id uuid NOT NULL DEFAULT gen_random_uuid()');
        DB::statement('ALTER TABLE oauth_auth_codes ALTER COLUMN user_id DROP DEFAULT');
        DB::statement('CREATE INDEX IF NOT EXISTS oauth_auth_codes_user_id_index ON oauth_auth_codes (user_id)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE oauth_access_tokens DROP COLUMN IF EXISTS user_id');
        DB::statement('ALTER TABLE oauth_access_tokens ADD COLUMN user_id bigint NULL');

        DB::statement('ALTER TABLE oauth_auth_codes DROP COLUMN IF EXISTS user_id');
        DB::statement('ALTER TABLE oauth_auth_codes ADD COLUMN user_id bigint NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE oauth_auth_codes ALTER COLUMN user_id DROP DEFAULT');
    }
};
