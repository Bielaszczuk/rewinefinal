<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WineComparison extends Model
{
    use HasUuids;

    protected $table = 'wine_comparisons';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['wine_a_id', 'wine_b_id', 'language', 'comparison_json'];

    protected $casts = [
        'comparison_json' => 'array',
    ];

    public function wineA(): BelongsTo
    {
        return $this->belongsTo(Wine::class, 'wine_a_id');
    }

    public function wineB(): BelongsTo
    {
        return $this->belongsTo(Wine::class, 'wine_b_id');
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }
}
