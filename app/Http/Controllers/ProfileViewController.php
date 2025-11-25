<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileViewController extends Controller
{
    /**
     * Display the specified user's public profile.
     */
    public function show(Request $request, $user): Response
    {
        // Find user by username (preferred) or id (fallback)
        $profileUser = User::where('username', $user)
            ->orWhere('id', $user)
            ->firstOrFail();
            
        // Always redirect to username-based URL if username exists (unless already using username)
        if ($profileUser->username && $user !== $profileUser->username) {
            return redirect()->route('profile.show', $profileUser->username);
        }
        
        // If user doesn't have a username and URL is not ID-based, redirect to ID-based URL
        if (!$profileUser->username && $user !== (string)$profileUser->id) {
            return redirect()->route('profile.show', $profileUser->id);
        }
        
        $isOwnProfile = auth()->check() && auth()->id() === $profileUser->id;
        
        // Get a fresh instance from the database to ensure we have the latest data
        $profileUser = $profileUser->fresh();
        
        // Load all relationships with fresh data
        $profileUser->load(['profile', 'posts' => function ($query) {
            $query->where('is_published', true)->latest();
        }]);

        // Get follow stats
        $followersCount = $profileUser->followers()->count();
        $followingCount = $profileUser->following()->count();
        $isFollowing = auth()->check() && !$isOwnProfile 
            ? $profileUser->isFollowedBy(auth()->id())
            : false;

        // Ensure profile is loaded (create if it doesn't exist)
        if (!$profileUser->profile) {
            $profileUser->profile()->create(['user_id' => $profileUser->id]);
            $profileUser->load('profile');
        }

        return Inertia::render('Profile/Show', [
            'profileUser' => $profileUser,
            'isOwnProfile' => $isOwnProfile,
            'followersCount' => $followersCount,
            'followingCount' => $followingCount,
            'isFollowing' => $isFollowing,
            'success' => session('success'),
        ]);
    }
}
