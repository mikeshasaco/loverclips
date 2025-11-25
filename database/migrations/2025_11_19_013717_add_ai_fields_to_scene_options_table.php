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
        Schema::table('scene_options', function (Blueprint $table) {
            $table->string('ai_intent_key', 100)->nullable()->after('order');
            $table->boolean('requires_tokens')->default(false)->after('ai_intent_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scene_options', function (Blueprint $table) {
            $table->dropColumn(['ai_intent_key', 'requires_tokens']);
        });
    }
};
