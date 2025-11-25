<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /**
     * Show the form for creating a new post.
     */
    public function create(): Response
    {
        return Inertia::render('Post/Create');
    }

    /**
     * Display a listing of draft (unpublished) posts for the authenticated user.
     */
    public function drafts(Request $request, string $username): Response
    {
        $user = $request->user();
        
        // Verify the username matches the authenticated user
        if ($user->username !== $username) {
            // Redirect to the correct username-based URL
            if ($user->username) {
                return redirect()->route('posts.drafts', $user->username);
            }
            abort(403, 'Unauthorized');
        }
        
        $drafts = Post::where('user_id', $user->id)
            ->where('is_published', false)
            ->with(['user'])
            ->latest()
            ->get();

        return Inertia::render('Post/Drafts', [
            'drafts' => $drafts,
        ]);
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'thumbnail' => 'nullable|image|max:10240',
            'price' => 'nullable|numeric|min:0',
            'is_paid' => 'boolean',
        ]);

        $post = new Post();
        $post->user_id = auth()->id();
        $post->title = $validated['title'];
        $post->description = $validated['description'] ?? null;
        $post->category = $validated['category'] ?? null;
        $post->price = $validated['price'] ?? null;
        $post->is_paid = $validated['is_paid'] ?? false;
        $post->is_published = false; // Start as draft

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $post->thumbnail_url = Storage::url($path);
        }

        $post->save();

        return redirect()->route('posts.edit', $post->id)->with('post', $post)->with('id', $post->id);
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post): Response
    {
        $this->authorize('update', $post);

        $post->load(['scenes.options.nextScene']);

        return Inertia::render('Post/Edit', [
            'post' => $post,
        ]);
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'thumbnail' => 'nullable|image|max:10240',
            'price' => 'nullable|numeric|min:0',
            'is_paid' => 'boolean',
            'is_published' => 'boolean',
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($post->thumbnail_url) {
                $oldPath = str_replace('/storage/', '', $post->thumbnail_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $validated['thumbnail_url'] = Storage::url($path);
        }

        $post->update($validated);

        // If post was just published, redirect to profile page
        if (isset($validated['is_published']) && $validated['is_published'] === true) {
            $user = $post->user;
            if ($user->username) {
                return redirect()->route('profile.show', $user->username);
            }
            return redirect()->route('profile.show', $user->id);
        }

        return redirect()->route('posts.edit', $post->id);
    }
}
