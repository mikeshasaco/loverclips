<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Post;
use Inertia\Inertia;

class PostViewController extends Controller
{
    public function show(string $username, string $slug)
    {
        // Find user by username
        $postUser = \App\Models\User::where('username', $username)->firstOrFail();
        
        // Find post by user_id and slug
        $post = Post::where('user_id', $postUser->id)
            ->where('slug', $slug)
            ->with(['user', 'welcomeScene.options'])
            ->firstOrFail();

        $user = auth()->user();
        $hasAccess = !$post->is_paid || ($user && $post->isPurchasedBy($user->id));

        // Don't load existing conversation - always start fresh
        // This ensures the chat restarts from the beginning every time
        $conversation = null;

        return Inertia::render('Post/Show', [
            'post' => $post,
            'hasAccess' => $hasAccess,
            'isOwner' => $user && $post->user_id === $user->id,
            'conversation' => $conversation, // Always null to start fresh
        ]);
    }
}
