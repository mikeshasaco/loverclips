<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scene extends Model
{
    protected $fillable = [
        'post_id',
        'video_url',
        'trimmed_video_url',
        'trim_start',
        'trim_end',
        'banner_url',
        'title',
        'display_text',
        'tip_prompt',
        'is_welcome',
    ];

    protected $casts = [
        'is_welcome' => 'boolean',
    ];

    /**
     * Get the post that owns the scene.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get all options for the scene.
     */
    public function options(): HasMany
    {
        return $this->hasMany(SceneOption::class)->orderBy('order');
    }

    /**
     * Get the next scene for an option.
     */
    public function nextScene(): BelongsTo
    {
        return $this->belongsTo(Scene::class, 'next_scene_id');
    }

    /**
     * Get all tips for the scene.
     */
    public function tips(): HasMany
    {
        return $this->hasMany(Tip::class);
    }
}
