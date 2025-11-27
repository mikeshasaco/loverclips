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
        Schema::table('profiles', function (Blueprint $table) {
            // Change avatar_url from string to longText to store base64 encoded images
            $table->longText('avatar_url')->nullable()->change();
            // Change banner_url from string to longText to store base64 encoded images
            $table->longText('banner_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Revert back to string (URLs)
            $table->string('avatar_url')->nullable()->change();
            $table->string('banner_url')->nullable()->change();
        });
    }
};

