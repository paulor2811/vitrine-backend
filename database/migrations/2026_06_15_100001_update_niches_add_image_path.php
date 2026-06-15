<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('niches', function (Blueprint $table) {
            if (! Schema::hasColumn('niches', 'image_path')) {
                $table->string('image_path')->nullable()->after('icon');
            }
        });
    }

    public function down(): void
    {
        Schema::table('niches', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
