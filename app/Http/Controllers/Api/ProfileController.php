<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;

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
            'banner' => 'nullable|image|max:10240', // 10MB max
        ]);

        // Update user name if provided
        if (isset($validated['name'])) {
            $user->update(['name' => $validated['name']]);
        }

        // Get or create profile
        $profile = $user->profile ?? new Profile(['user_id' => $user->id]);

        // Handle avatar upload - store as base64 in database
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $imageData = file_get_contents($file->getRealPath());
            $base64 = base64_encode($imageData);
            $mimeType = $file->getMimeType();
            // Store as data URI: data:image/jpeg;base64,{base64_string}
            $profile->avatar_url = 'data:' . $mimeType . ';base64,' . $base64;
        }

        // Handle banner upload - store as base64 in database
        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            $imageData = file_get_contents($file->getRealPath());
            $base64 = base64_encode($imageData);
            $mimeType = $file->getMimeType();
            // Store as data URI: data:image/jpeg;base64,{base64_string}
            $profile->banner_url = 'data:' . $mimeType . ';base64,' . $base64;
        }

        // Update bio
        if (isset($validated['bio'])) {
            $profile->bio = $validated['bio'];
        }

        $profile->save();

        return response()->json($user->load('profile'));
    }
}
