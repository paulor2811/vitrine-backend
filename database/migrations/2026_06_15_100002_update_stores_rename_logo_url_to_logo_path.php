<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'logo_url') && ! Schema::hasColumn('stores', 'logo_path')) {
                $table->renameColumn('logo_url', 'logo_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'logo_path') && ! Schema::hasColumn('stores', 'logo_url')) {
                $table->renameColumn('logo_path', 'logo_url');
            }
        });
    }
};
