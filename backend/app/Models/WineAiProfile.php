<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WineAiProfile extends Model
{
    use HasUuids;

    protected $table = 'wine_ai_profiles';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['wine_id', 'language', 'profile_json'];

    protected $casts = [
        'profile_json' => 'array',
    ];

    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class, 'wine_id');
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }
}
