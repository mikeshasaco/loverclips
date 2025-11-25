<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the specified profile.
     */
    public function show(string $id)
    {
        $user = User::with(['profile', 'posts' => function ($query) {
            $query->where('is_published', true)->latest();
        }])->findOrFail($id);

        return response()->json($user);
    }

    /**
     * Update the specified profile.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        // Check ownership
        if ($user->id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|image|max:5120', // 5MB max
        ]);

        // Update user name if provided
        if (isset($validated['name'])) {
            $user->update(['name' => $validated['name']]);
        }

        // Get or create profile
        $profile = $user->profile ?? new Profile(['user_id' => $user->id]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($profile->avatar_url) {
                $oldPath = str_replace('/storage/', '', $profile->avatar_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $profile->avatar_url = Storage::url($path);
        }

        // Update bio
        if (isset($validated['bio'])) {
            $profile->bio = $validated['bio'];
        }

        $profile->save();

        return response()->json($user->load('profile'));
    }
}
