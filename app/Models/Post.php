<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'category',
        'thumbnail_url',
        'banner_url',
        'price',
        'is_paid',
        'is_published',
        'welcome_scene_id',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_published' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Get the user that owns the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the welcome scene for the post.
     */
    public function welcomeScene(): BelongsTo
    {
        return $this->belongsTo(Scene::class, 'welcome_scene_id');
    }

    /**
     * Get all scenes for the post.
     */
    public function scenes(): HasMany
    {
        return $this->hasMany(Scene::class);
    }

    /**
     * Get all tips for the post.
     */
    public function tips(): HasMany
    {
        return $this->hasMany(Tip::class);
    }

    /**
     * Get all purchases for the post.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get all conversations for this post.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Check if a user has purchased this post.
     */
    public function isPurchasedBy($userId): bool
    {
        return $this->purchases()->where('user_id', $userId)->exists();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = static::generateUniqueSlug($post->title, $post->user_id);
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title') && empty($post->slug)) {
                $post->slug = static::generateUniqueSlug($post->title, $post->user_id, $post->id);
            }
        });
    }

    /**
     * Generate a unique slug for the post.
     */
    protected static function generateUniqueSlug($title, $userId, $excludeId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('user_id', $userId)
            ->where('slug', $slug)
            ->when($excludeId, function ($query) use ($excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
