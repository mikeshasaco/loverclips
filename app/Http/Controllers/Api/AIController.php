<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Scene;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AIController extends Controller
{
    /**
     * Generate scene options using AI.
     */
    public function generateSceneOptions(Request $request)
    {
        $validated = $request->validate([
            'scene_id' => 'required|exists:scenes,id',
            'video_description' => 'nullable|string|max:1000',
        ]);

        $scene = Scene::with('post')->findOrFail($validated['scene_id']);

        // Check ownership
        if ($scene->post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $apiKey = config('services.openai.api_key');
        
        if (!$apiKey) {
            return response()->json([
                'message' => 'AI service not configured',
                'option_text_1' => 'Continue the story',
                'option_text_2' => 'Take a different path',
            ]);
        }

        // Build prompt
        $prompt = "Given this interactive video scene";
        if ($validated['video_description']) {
            $prompt .= " description: " . $validated['video_description'];
        } else {
            $prompt .= " titled: " . $scene->title;
        }
        $prompt .= ", generate two short, engaging interactive option texts (each under 50 characters) that a viewer could click to continue the story. Make them interesting and contextually relevant. Return only the two options, one per line, without numbering or labels.";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a creative assistant that generates engaging interactive story options.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens' => 100,
                'temperature' => 0.8,
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'];
                $lines = array_filter(array_map('trim', explode("\n", $content)));
                
                $option1 = isset($lines[0]) ? trim($lines[0], '- •1234567890. ') : 'Continue';
                $option2 = isset($lines[1]) ? trim($lines[1], '- •1234567890. ') : 'Explore';

                return response()->json([
                    'option_text_1' => $option1,
                    'option_text_2' => $option2,
                ]);
            }
        } catch (\Exception $e) {
            // Fallback to default options
        }

        return response()->json([
            'option_text_1' => 'Continue the story',
            'option_text_2' => 'Take a different path',
        ]);
    }
}
