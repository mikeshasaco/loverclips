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
        $posts = \App\Models\Post::whereNull('slug')->get();
        
        foreach ($posts as $post) {
            $baseSlug = \Illuminate\Support\Str::slug($post->title);
            $slug = $baseSlug;
            $counter = 1;
            
            while (\App\Models\Post::where('user_id', $post->user_id)
                ->where('slug', $slug)
                ->where('id', '!=', $post->id)
                ->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $post->slug = $slug;
            $post->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse - slugs can remain
    }
};
