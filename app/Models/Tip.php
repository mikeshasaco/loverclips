<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tip extends Model
{
    protected $fillable = [
        'user_id',
        'post_id',
        'scene_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that gave the tip.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post that received the tip.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the scene that received the tip.
     */
    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }
}
