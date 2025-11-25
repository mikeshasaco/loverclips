<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('scenes', function (Blueprint $table) {
            // SQLite doesn't support AFTER clause, so we just add the column
            if (!Schema::hasColumn('scenes', 'banner_url')) {
                $table->string('banner_url')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->dropColumn('banner_url');
        });
    }
};
