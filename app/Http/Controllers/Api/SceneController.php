<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Scene;
use App\Models\SceneOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SceneController extends Controller
{
    /**
     * Display a listing of scenes for a post.
     */
    public function index(string $postId)
    {
        $post = Post::findOrFail($postId);

        // Check ownership
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $scenes = Scene::where('post_id', $postId)
            ->with(['options.nextScene'])
            ->get();

        return response()->json($scenes);
    }

    /**
     * Store a newly created scene.
     */
    public function store(Request $request, string $postId)
    {
        $post = Post::findOrFail($postId);

        // Check ownership
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $validated = $request->validate([
                'video' => 'required|mimes:mp4,mov,avi|max:153600', // 150MB max
                'banner' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:10240', // 10MB max
                'title' => 'required|string|max:255',
                'display_text' => 'nullable|string',
                'tip_prompt' => 'nullable|string',
                'is_welcome' => ['sometimes', 'boolean'],
                'trim_start' => 'nullable|numeric|min:0',
                'trim_end' => 'nullable|numeric|min:0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // Handle video upload
        if (!$request->hasFile('video')) {
            return response()->json(['message' => 'Video file is required'], 422);
        }
        
        $videoFile = $request->file('video');
        $path = $videoFile->store('videos', 'public');
        $videoUrl = Storage::url($path);

        // Store trim parameters if provided
        $trimStart = null;
        $trimEnd = null;
        $trimmedVideoUrl = null;
        
        if ($request->has('trim_start') && $request->has('trim_end')) {
            $trimStart = (float) $request->input('trim_start');
            $trimEnd = (float) $request->input('trim_end');
            
            // Create trimmed video file
            try {
                $trimmedVideoUrl = $this->createTrimmedVideo($videoFile, $trimStart, $trimEnd, $path);
                
                \Log::info('Scene creation with trim info', [
                    'trim_start' => $trimStart,
                    'trim_end' => $trimEnd,
                    'duration' => $trimEnd - $trimStart,
                    'trimmed_video_url' => $trimmedVideoUrl,
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to create trimmed video', [
                    'error' => $e->getMessage(),
                    'trim_start' => $trimStart,
                    'trim_end' => $trimEnd,
                ]);
                // Continue without trimmed video - will use full video with client-side trimming
            }
        } else {
            \Log::info('Scene creation without trim info', [
                'has_trim_start' => $request->has('trim_start'),
                'has_trim_end' => $request->has('trim_end'),
            ]);
        }

        // Handle banner upload
        $bannerUrl = null;
        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('scene-banners', 'public');
            $bannerUrl = Storage::url($bannerPath);
        }

        try {
            $scene = Scene::create([
                'post_id' => $postId,
                'video_url' => $videoUrl, // Full video (kept for re-editing)
                'trimmed_video_url' => $trimmedVideoUrl, // Trimmed video (used in conversations)
                'trim_start' => $trimStart,
                'trim_end' => $trimEnd,
                'banner_url' => $bannerUrl,
                'title' => $validated['title'],
                'display_text' => $validated['display_text'] ?? null,
                'tip_prompt' => $validated['tip_prompt'] ?? null,
                'is_welcome' => filter_var($request->input('is_welcome', false), FILTER_VALIDATE_BOOLEAN),
            ]);
            
            // Log what was actually saved
            \Log::info('Scene created successfully', [
                'scene_id' => $scene->id,
                'trim_start' => $scene->trim_start,
                'trim_end' => $scene->trim_end,
                'has_trim' => $scene->trim_start !== null && $scene->trim_end !== null,
            ]);

            // If this is the welcome scene, update the post
            if ($scene->is_welcome) {
                $post->update(['welcome_scene_id' => $scene->id]);
            }

            $scene->load('options');
            
            // Ensure the scene has an id before returning
            if (!$scene->id) {
                \Log::error('Scene created but has no ID', ['scene' => $scene->toArray()]);
                return response()->json([
                    'message' => 'Scene created but ID is missing',
                ], 500);
            }
            
            return response()->json($scene, 201);
        } catch (\Exception $e) {
            \Log::error('Error creating scene: ' . $e->getMessage(), [
                'post_id' => $postId,
                'error' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'message' => 'Failed to create scene',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified scene.
     */
    public function show(string $id)
    {
        $scene = Scene::with(['post', 'options.nextScene'])->findOrFail($id);

        // Check if user has access to the post
        $post = $scene->post;
        $user = auth()->user();
        
        if ($post->is_paid && (!$user || !$post->isPurchasedBy($user->id))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($scene);
    }

    /**
     * Update the specified scene.
     */
    public function update(Request $request, string $id)
    {
        $scene = Scene::with('post')->findOrFail($id);

        // Check ownership
        if ($scene->post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'video' => 'sometimes|mimes:mp4,mov,avi|max:102400',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:10240',
            'title' => 'sometimes|string|max:255',
            'display_text' => 'nullable|string',
            'tip_prompt' => 'nullable|string',
            'is_welcome' => 'boolean',
        ]);

        // Handle video upload
        if ($request->hasFile('video')) {
            // Delete old video
            if ($scene->video_url) {
                $oldPath = str_replace('/storage/', '', $scene->video_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('video')->store('videos', 'public');
            $validated['video_url'] = Storage::url($path);
        }

        // Handle banner upload
        if ($request->hasFile('banner')) {
            // Delete old banner
            if ($scene->banner_url) {
                $oldBannerPath = str_replace('/storage/', '', $scene->banner_url);
                Storage::disk('public')->delete($oldBannerPath);
            }

            $bannerPath = $request->file('banner')->store('scene-banners', 'public');
            $validated['banner_url'] = Storage::url($bannerPath);
        }

        $scene->update($validated);

        // If this is the welcome scene, update the post
        if ($scene->is_welcome) {
            $scene->post->update(['welcome_scene_id' => $scene->id]);
        }

        return response()->json($scene->load('options.nextScene'));
    }

    /**
     * Remove the specified scene.
     */
    public function destroy(string $id)
    {
        $scene = Scene::with('post')->findOrFail($id);

        // Check ownership
        if ($scene->post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete video
        if ($scene->video_url) {
            $oldPath = str_replace('/storage/', '', $scene->video_url);
            Storage::disk('public')->delete($oldPath);
        }

        $scene->delete();

        return response()->json(['message' => 'Scene deleted successfully']);
    }

    /**
     * Get options for a scene.
     */
    public function getOptions(string $id)
    {
        $scene = Scene::with('post')->findOrFail($id);

        // Check ownership
        if ($scene->post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $options = $scene->options()->with('nextScene')->get();

        return response()->json($options);
    }

    /**
     * Store a new option for a scene.
     */
    public function storeOption(Request $request, string $id)
    {
        $scene = Scene::with('post')->findOrFail($id);

        // Check ownership
        if ($scene->post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'option_text' => 'required|string|max:255',
            'next_scene_id' => 'nullable|exists:scenes,id',
            'order' => 'required|integer|min:1',
            'ai_intent_key' => 'nullable|string|max:100',
            'requires_tokens' => 'boolean',
        ]);

        // Check if option with this order already exists
        $existingOption = SceneOption::where('scene_id', $id)
            ->where('order', $validated['order'])
            ->first();

        if ($existingOption) {
            return response()->json(['message' => 'Option with this order already exists'], 422);
        }

        $option = SceneOption::create([
            'scene_id' => $id,
            'option_text' => $validated['option_text'],
            'next_scene_id' => $validated['next_scene_id'] ?? null,
            'order' => $validated['order'],
            'ai_intent_key' => $validated['ai_intent_key'] ?? null,
            'requires_tokens' => $validated['requires_tokens'] ?? false,
        ]);

        return response()->json($option->load('nextScene'), 201);
    }

    /**
     * Update the specified option.
     */
    public function updateOption(Request $request, string $id)
    {
        $option = SceneOption::with('scene.post')->findOrFail($id);

        // Check ownership
        if ($option->scene->post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'option_text' => 'sometimes|string|max:255',
            'next_scene_id' => 'nullable|exists:scenes,id',
            'order' => 'sometimes|integer|min:1',
            'ai_intent_key' => 'nullable|string|max:100',
            'requires_tokens' => 'boolean',
        ]);

        $option->update($validated);

        return response()->json($option->load('nextScene'));
    }

    /**
     * Remove the specified option.
     */
    public function destroyOption(string $id)
    {
        $option = SceneOption::with('scene.post')->findOrFail($id);

        // Check ownership
        if ($option->scene->post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $option->delete();

        return response()->json(['message' => 'Option deleted successfully']);
    }

    /**
     * Create a trimmed video file from the original video.
     * 
     * @param \Illuminate\Http\UploadedFile $videoFile
     * @param float $trimStart
     * @param float $trimEnd
     * @param string $originalPath
     * @return string|null URL of the trimmed video, or null if trimming fails
     */
    private function createTrimmedVideo($videoFile, $trimStart, $trimEnd, $originalPath)
    {
        // Check if ffmpeg is available
        $ffmpegPath = $this->findFfmpegPath();
        if (!$ffmpegPath) {
            \Log::warning('ffmpeg not found - cannot create trimmed video');
            return null;
        }

        $duration = $trimEnd - $trimStart;
        
        // Create trimmed video filename
        $originalName = pathinfo($originalPath, PATHINFO_FILENAME);
        $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
        $trimmedPath = 'videos/trimmed_' . $originalName . '_' . $trimStart . '_' . $trimEnd . '.' . $extension;
        $fullTrimmedPath = storage_path('app/public/' . $trimmedPath);
        
        // Ensure directory exists
        $trimmedDir = dirname($fullTrimmedPath);
        if (!is_dir($trimmedDir)) {
            mkdir($trimmedDir, 0755, true);
        }

        // Get the full path to the original video
        $originalFullPath = storage_path('app/public/' . $originalPath);

        // Build ffmpeg command to trim video
        // -ss: start time
        // -t: duration (not end time)
        // -c copy: copy codecs (fast, no re-encoding) - but may not work for all videos
        // -avoid_negative_ts make_zero: handle timestamp issues
        $command = sprintf(
            '%s -ss %.3f -i %s -t %.3f -c copy -avoid_negative_ts make_zero -y %s 2>&1',
            escapeshellarg($ffmpegPath),
            $trimStart,
            escapeshellarg($originalFullPath),
            $duration,
            escapeshellarg($fullTrimmedPath)
        );

        \Log::info('Creating trimmed video', [
            'command' => $command,
            'trim_start' => $trimStart,
            'trim_end' => $trimEnd,
            'duration' => $duration,
        ]);

        // Execute ffmpeg command
        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($fullTrimmedPath)) {
            \Log::error('ffmpeg trimming failed', [
                'return_code' => $returnCode,
                'output' => implode("\n", $output),
                'trimmed_path' => $fullTrimmedPath,
            ]);
            
            // Try with re-encoding if copy failed
            $command = sprintf(
                '%s -ss %.3f -i %s -t %.3f -c:v libx264 -c:a aac -avoid_negative_ts make_zero -y %s 2>&1',
                escapeshellarg($ffmpegPath),
                $trimStart,
                escapeshellarg($originalFullPath),
                $duration,
                escapeshellarg($fullTrimmedPath)
            );
            
            exec($command, $output2, $returnCode2);
            
            if ($returnCode2 !== 0 || !file_exists($fullTrimmedPath)) {
                \Log::error('ffmpeg trimming with re-encoding also failed', [
                    'return_code' => $returnCode2,
                    'output' => implode("\n", $output2),
                ]);
                return null;
            }
        }

        // Return the URL to the trimmed video
        return Storage::url($trimmedPath);
    }

    /**
     * Find the path to ffmpeg executable.
     * 
     * @return string|null
     */
    private function findFfmpegPath()
    {
        // Common paths for ffmpeg
        $possiblePaths = [
            '/usr/local/bin/ffmpeg',
            '/usr/bin/ffmpeg',
            '/opt/homebrew/bin/ffmpeg', // macOS Apple Silicon
            exec('which ffmpeg 2>/dev/null'),
        ];

        foreach ($possiblePaths as $path) {
            if ($path && is_executable($path)) {
                return $path;
            }
        }

        return null;
    }
}
