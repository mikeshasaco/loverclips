<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    /**
     * Follow a user.
     */
    public function follow(Request $request, $userId)
    {
        $userToFollow = User::findOrFail($userId);
        $currentUser = $request->user();

        // Can't follow yourself
        if ($currentUser->id === $userToFollow->id) {
            return response()->json(['message' => 'You cannot follow yourself'], 400);
        }

        // Check if already following
        $existingFollow = Follow::where('follower_id', $currentUser->id)
            ->where('following_id', $userToFollow->id)
            ->first();

        if ($existingFollow) {
            return response()->json(['message' => 'You are already following this user'], 400);
        }

        // Create follow relationship
        Follow::create([
            'follower_id' => $currentUser->id,
            'following_id' => $userToFollow->id,
        ]);

        return response()->json([
            'message' => 'Successfully followed user',
            'is_following' => true,
            'followers_count' => $userToFollow->followers()->count(),
        ]);
    }

    /**
     * Unfollow a user.
     */
    public function unfollow(Request $request, $userId)
    {
        $userToUnfollow = User::findOrFail($userId);
        $currentUser = $request->user();

        // Find and delete follow relationship
        $follow = Follow::where('follower_id', $currentUser->id)
            ->where('following_id', $userToUnfollow->id)
            ->first();

        if (!$follow) {
            return response()->json(['message' => 'You are not following this user'], 400);
        }

        $follow->delete();

        return response()->json([
            'message' => 'Successfully unfollowed user',
            'is_following' => false,
            'followers_count' => $userToUnfollow->followers()->count(),
        ]);
    }

    /**
     * Check if current user is following a user.
     */
    public function check(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $currentUser = $request->user();

        $isFollowing = $user->isFollowedBy($currentUser->id);

        return response()->json([
            'is_following' => $isFollowing,
            'followers_count' => $user->followers()->count(),
            'following_count' => $user->following()->count(),
        ]);
    }
}
