<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Get latest posts for the slider (limit to 20 most recent)
        $latestPosts = Post::where('is_published', true)
            ->with(['user'])
            ->latest()
            ->limit(20)
            ->get();

        // Get all posts (for category filtering if needed)
        $query = Post::where('is_published', true)
            ->with(['user'])
            ->latest();

        // Filter by category if provided
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        $posts = $query->get();

        // Get all unique categories from published posts
        $categories = Post::where('is_published', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values()
            ->toArray();

        return Inertia::render('Home', [
            'latestPosts' => $latestPosts,
            'posts' => $posts,
            'categories' => $categories,
            'selectedCategory' => $request->category,
        ]);
    }
}
