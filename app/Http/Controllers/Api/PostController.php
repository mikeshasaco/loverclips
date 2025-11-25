<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    /**
     * Display a listing of published posts.
     */
    public function index()
    {
        $posts = Post::where('is_published', true)
            ->with(['user', 'welcomeScene'])
            ->latest()
            ->get();

        return response()->json($posts);
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'nullable|string|max:255',
                'thumbnail' => 'nullable|image|max:10240', // 10MB max
                'banner' => 'nullable|image|max:10240', // 10MB max
                'price' => 'nullable|numeric|min:0',
                'is_paid' => 'boolean',
                'is_published' => 'boolean',
            ]);

            // Only pass database fields to Post model (exclude file uploads)
            $postData = array_filter([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'] ?? null,
                'price' => $validated['price'] ?? null,
                'is_paid' => $validated['is_paid'] ?? false,
                'is_published' => $validated['is_published'] ?? false,
            ], fn($value) => $value !== null);

            $post = new Post($postData);
            $post->user_id = auth()->id();

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                try {
                    $path = $request->file('thumbnail')->store('thumbnails', 'public');
                    $post->thumbnail_url = Storage::url($path);
                } catch (\Exception $e) {
                    \Log::error('Error storing thumbnail: ' . $e->getMessage());
                    return response()->json(['message' => 'Failed to upload thumbnail'], 500);
                }
            }

            // Handle banner upload
            if ($request->hasFile('banner')) {
                try {
                    $path = $request->file('banner')->store('post-banners', 'public');
                    $post->banner_url = Storage::url($path);
                } catch (\Exception $e) {
                    \Log::error('Error storing banner: ' . $e->getMessage());
                    return response()->json(['message' => 'Failed to upload banner'], 500);
                }
            }

            $post->save();

            return response()->json($post->load('user'), 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating post: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Failed to create post',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Display the specified post.
     */
    public function show(string $id)
    {
        $post = Post::with(['user', 'welcomeScene', 'scenes.options.nextScene'])
            ->findOrFail($id);

        // Check if user has access
        $user = auth()->user();
        if ($post->is_paid && (!$user || !$post->isPurchasedBy($user->id))) {
            return response()->json([
                'post' => $post->only(['id', 'title', 'description', 'thumbnail_url', 'price', 'is_paid', 'user']),
                'has_access' => false,
            ]);
        }

        return response()->json([
            'post' => $post,
            'has_access' => true,
        ]);
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, string $id)
    {
        $post = Post::findOrFail($id);

        // Check ownership
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'thumbnail' => 'nullable|image|max:10240',
            'banner' => 'nullable|image|max:10240',
            'price' => 'nullable|numeric|min:0',
            'is_paid' => 'boolean',
            'is_published' => 'boolean',
            'welcome_scene_id' => 'nullable|exists:scenes,id',
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($post->thumbnail_url) {
                $oldPath = str_replace('/storage/', '', $post->thumbnail_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $validated['thumbnail_url'] = Storage::url($path);
        }

        // Handle banner upload
        if ($request->hasFile('banner')) {
            // Delete old banner
            if ($post->banner_url) {
                $oldPath = str_replace('/storage/', '', $post->banner_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('banner')->store('post-banners', 'public');
            $validated['banner_url'] = Storage::url($path);
        }

        $post->update($validated);

        return response()->json($post->load('user', 'welcomeScene'));
    }

    /**
     * Remove the specified post.
     */
    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);

        // Check ownership
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete thumbnail
        if ($post->thumbnail_url) {
            $oldPath = str_replace('/storage/', '', $post->thumbnail_url);
            Storage::disk('public')->delete($oldPath);
        }

        // Delete banner
        if ($post->banner_url) {
            $oldPath = str_replace('/storage/', '', $post->banner_url);
            Storage::disk('public')->delete($oldPath);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }
}
