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

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            if ($profile->avatar_url) {
                $oldPath = str_replace('/storage/', '', $profile->avatar_url);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $profile->avatar_url = \Illuminate\Support\Facades\Storage::url($path);
        }

        // Handle banner upload
        if ($request->hasFile('banner')) {
            if ($profile->banner_url) {
                $oldPath = str_replace('/storage/', '', $profile->banner_url);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('banner')->store('banners', 'public');
            $profile->banner_url = \Illuminate\Support\Facades\Storage::url($path);
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
