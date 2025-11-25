<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'scene_id',
        'option_id',
        'sender_type',
        'text',
        'video_url',
    ];

    protected $casts = [
        'sender_type' => 'string',
    ];

    /**
     * Get the conversation that owns this message.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the user that sent this message (if user message).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the scene associated with this message (if girl/system message).
     */
    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }

    /**
     * Get the option that was clicked (if user message).
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(SceneOption::class, 'option_id');
    }
}
