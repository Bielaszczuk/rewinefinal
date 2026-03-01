<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Review extends Model
{
    use HasUuids;

    protected $table = 'reviews';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'wine_id', 'user_id', 'rating', 'title', 'comment',
        'is_verified', 'helpful_count', 'likes_count',
    ];

    protected $casts = [
        'rating' => 'float',
        'is_verified' => 'boolean',
        'helpful_count' => 'integer',
        'likes_count' => 'integer',
    ];

    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class, 'wine_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ReviewLike::class, 'review_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ReviewComment::class, 'review_id');
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }
}
