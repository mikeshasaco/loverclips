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
            $table->decimal('trim_start', 10, 2)->nullable()->after('video_url');
            $table->decimal('trim_end', 10, 2)->nullable()->after('trim_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->dropColumn(['trim_start', 'trim_end']);
        });
    }
};
