<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request, string $usernameOrId): Response
    {
        $user = $request->user();
        
        // Verify the route parameter matches the authenticated user
        // Allow both username and ID to support users without usernames
        if ($user->username && $usernameOrId !== $user->username && (string)$usernameOrId !== (string)$user->id) {
            // Redirect to the correct username-based URL if username exists
            return redirect()->route('settings.edit', $user->username);
        }
        
        // If user doesn't have username and param is not their ID, deny access
        if (!$user->username && (string)$usernameOrId !== (string)$user->id) {
            abort(403, 'Unauthorized');
        }
        
        $profile = $user->profile;
        $posts = $user->posts()->with(['welcomeScene'])->latest()->get();
        
        return Inertia::render('Settings/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'profile' => $profile ? [
                'bio' => $profile->bio,
                'location' => $profile->location,
                'avatar_url' => $profile->avatar_url,
                'banner_url' => $profile->banner_url,
            ] : null,
            'posts' => $posts,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, string $usernameOrId): RedirectResponse
    {
        $user = $request->user();
        
        // Verify the route parameter matches the authenticated user
        // Allow both username and ID to support users without usernames
        if ($user->username && $usernameOrId !== $user->username && (string)$usernameOrId !== (string)$user->id) {
            // Redirect to the correct username-based URL if username exists
            return redirect()->route('settings.edit', $user->username);
        }
        
        // If user doesn't have username and param is not their ID, deny access
        if (!$user->username && (string)$usernameOrId !== (string)$user->id) {
            abort(403, 'Unauthorized');
        }

        // Get or create profile
        if (!$user->profile) {
            $profile = $user->profile()->create(['user_id' => $user->id]);
        } else {
            $profile = $user->profile;
        }

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

        // Update bio and location - always update from input() if present
        // This handles both regular form data and FormData
        $bio = $request->input('bio');
        $location = $request->input('location');
        
        // Update bio - set to null if empty string, otherwise use trimmed value
        $profile->bio = ($bio !== null && trim($bio) !== '') ? trim($bio) : null;
        
        // Update location - set to null if empty string, otherwise use trimmed value
        $profile->location = ($location !== null && trim($location) !== '') ? trim($location) : null;
        
        // Save the profile
        $profile->save();
        
        // Refresh the user model to ensure the relationship is updated
        $user->refresh();
        $user->load('profile');

        // Redirect to profile page with success message
        if ($user->username) {
            return Redirect::route('profile.show', $user->username)->with('success', 'Profile updated successfully!');
        }
        return Redirect::route('profile.show', $user->id)->with('success', 'Profile updated successfully!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request, string $usernameOrId): RedirectResponse
    {
        $user = $request->user();
        
        // Verify the route parameter matches the authenticated user
        if ($user->username && $usernameOrId !== $user->username && (string)$usernameOrId !== (string)$user->id) {
            abort(403, 'Unauthorized');
        }
        
        if (!$user->username && (string)$usernameOrId !== (string)$user->id) {
            abort(403, 'Unauthorized');
        }
        
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
