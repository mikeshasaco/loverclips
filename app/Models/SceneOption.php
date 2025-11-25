<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SceneOption extends Model
{
    protected $fillable = [
        'scene_id',
        'option_text',
        'next_scene_id',
        'order',
        'ai_intent_key',
        'requires_tokens',
    ];

    protected $casts = [
        'order' => 'integer',
        'requires_tokens' => 'boolean',
    ];

    /**
     * Get the scene that owns the option.
     */
    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }

    /**
     * Get the next scene for this option.
     */
    public function nextScene(): BelongsTo
    {
        return $this->belongsTo(Scene::class, 'next_scene_id');
    }
}
