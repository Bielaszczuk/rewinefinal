<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ReviewComment extends Model
{
    use HasUuids;

    protected $table = 'review_comments';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['review_id', 'user_id', 'content'];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class, 'review_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }
}
