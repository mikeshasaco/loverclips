<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Post;
use App\Models\Scene;
use App\Models\SceneOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    /**
     * Start a new conversation with a post.
     */
    public function start(Request $request, $postId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $post = Post::with('welcomeScene.options')->findOrFail($postId);

        // Check if user has access
        if ($post->is_paid && !$post->isPurchasedBy($user->id)) {
            return response()->json(['message' => 'Post must be purchased first'], 403);
        }

        // Always start fresh - delete any existing active conversations for this user/post
        // This ensures the conversation always starts from the beginning
        Conversation::where('user_id', $user->id)
            ->where('post_id', $postId)
            ->where('status', 'active')
            ->delete();

        // Create new conversation starting from the beginning
        $conversation = Conversation::create([
            'user_id' => $user->id,
            'post_id' => $postId,
            'current_scene_id' => $post->welcome_scene_id,
            'status' => 'active',
        ]);

        // Load the welcome scene if it exists
        if ($post->welcome_scene_id) {
            $scene = Scene::with('options')->find($post->welcome_scene_id);
            
            // Create message for the welcome scene
            // Use trimmed_video_url if available, otherwise fall back to video_url
            $videoUrl = $scene->trimmed_video_url ?? $scene->video_url;
            
            $message = ConversationMessage::create([
                'conversation_id' => $conversation->id,
                'scene_id' => $scene->id,
                'sender_type' => 'girl',
                'text' => $scene->display_text ?? $scene->title,
                'video_url' => $videoUrl,
            ]);
            
            // Store trim info in message (we'll add these columns to conversation_messages)
            // For now, we'll pass it through the scene relationship
        }

        // Load conversation with messages
        $conversation->load(['messages.scene', 'messages.option', 'currentScene.options']);

        return response()->json([
            'conversation' => $conversation,
        ]);
    }

    /**
     * Reply to a scene by selecting an option.
     */
    public function reply(Request $request, $conversationId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $conversation = Conversation::findOrFail($conversationId);

        // Check ownership
        if ($conversation->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'option_id' => 'required|exists:scene_options,id',
        ]);

        $option = SceneOption::findOrFail($validated['option_id']);

        // Check if option belongs to current scene
        if ($option->scene_id !== $conversation->current_scene_id) {
            return response()->json(['message' => 'Invalid option for current scene'], 400);
        }

        // Check if option requires tokens (premium)
        if ($option->requires_tokens) {
            // TODO: Check if user has tokens/premium access
            // For now, we'll allow it
        }

        // Create user message (the option they selected)
        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'option_id' => $option->id,
            'sender_type' => 'user',
            'text' => $option->option_text,
        ]);

        // Move to next scene
        if ($option->next_scene_id) {
            $nextScene = Scene::with('options')->find($option->next_scene_id);
            
            // Update conversation current scene
            $conversation->update([
                'current_scene_id' => $nextScene->id,
            ]);

            // Create girl message for next scene
            // Use trimmed_video_url if available, otherwise fall back to video_url
            $videoUrl = $nextScene->trimmed_video_url ?? $nextScene->video_url;
            
            ConversationMessage::create([
                'conversation_id' => $conversation->id,
                'scene_id' => $nextScene->id,
                'sender_type' => 'girl',
                'text' => $nextScene->display_text ?? $nextScene->title,
                'video_url' => $videoUrl,
            ]);
            
            // Trim info is available through the scene relationship
        } else {
            // End of conversation
            $conversation->update([
                'status' => 'ended',
                'current_scene_id' => null,
            ]);

            ConversationMessage::create([
                'conversation_id' => $conversation->id,
                'sender_type' => 'system',
                'text' => 'End of story. Thanks for watching!',
            ]);
        }

        // Reload conversation with messages
        $conversation->load(['messages.scene', 'messages.option', 'currentScene.options']);

        return response()->json([
            'conversation' => $conversation,
        ]);
    }

    /**
     * Get conversation messages.
     */
    public function messages($conversationId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $conversation = Conversation::with(['messages.scene', 'messages.option', 'currentScene.options'])
            ->findOrFail($conversationId);

        // Check ownership
        if ($conversation->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'conversation' => $conversation,
        ]);
    }
}
