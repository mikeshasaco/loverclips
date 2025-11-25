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
        Schema::create('scene_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scene_id')->constrained()->onDelete('cascade');
            $table->string('option_text');
            $table->foreignId('next_scene_id')->nullable()->constrained('scenes')->onDelete('set null');
            $table->integer('order')->default(1); // 1 or 2
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scene_options');
    }
};
