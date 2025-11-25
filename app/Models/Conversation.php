<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'user_id',
        'post_id',
        'current_scene_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the user that owns the conversation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post for this conversation.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the current scene.
     */
    public function currentScene(): BelongsTo
    {
        return $this->belongsTo(Scene::class, 'current_scene_id');
    }

    /**
     * Get all messages in this conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class)->orderBy('created_at');
    }
}
