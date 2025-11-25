<?php

use App\Http\Controllers\Api\AIController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SceneController;
use App\Http\Controllers\ConversationController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{id}', [PostController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/auth/me', function () {
        return response()->json(auth()->user());
    });

    // Profiles
    Route::get('/profiles/{id}', [ProfileController::class, 'show']);
    Route::put('/profiles/{id}', [ProfileController::class, 'update']);

    // Posts
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);

    // Scenes
    Route::get('/posts/{postId}/scenes', [SceneController::class, 'index']);
    Route::post('/posts/{postId}/scenes', [SceneController::class, 'store']);
    Route::get('/scenes/{id}', [SceneController::class, 'show']);
    Route::put('/scenes/{id}', [SceneController::class, 'update']);
    Route::delete('/scenes/{id}', [SceneController::class, 'destroy']);

    // Scene Options
    Route::get('/scenes/{id}/options', [SceneController::class, 'getOptions']);
    Route::post('/scenes/{id}/options', [SceneController::class, 'storeOption']);
    Route::put('/options/{id}', [SceneController::class, 'updateOption']);
    Route::delete('/options/{id}', [SceneController::class, 'destroyOption']);

    // Payments
    Route::post('/purchase', [PaymentController::class, 'purchase']);
    Route::post('/tip', [PaymentController::class, 'tip']);
    Route::post('/webhook/stripe', [PaymentController::class, 'webhook']);

    // AI
    Route::post('/ai/scene-options', [AIController::class, 'generateSceneOptions']);

    // Conversations
    Route::post('/posts/{postId}/conversations/start', [ConversationController::class, 'start']);
    Route::post('/conversations/{conversationId}/reply', [ConversationController::class, 'reply']);
    Route::get('/conversations/{conversationId}/messages', [ConversationController::class, 'messages']);

    // Follows
    Route::post('/users/{userId}/follow', [\App\Http\Controllers\Api\FollowController::class, 'follow']);
    Route::delete('/users/{userId}/follow', [\App\Http\Controllers\Api\FollowController::class, 'unfollow']);
    Route::get('/users/{userId}/follow', [\App\Http\Controllers\Api\FollowController::class, 'check']);
});

