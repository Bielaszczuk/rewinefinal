<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Wine extends Model
{
    use HasUuids;

    protected $table = 'wines';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'winery_id', 'name', 'vintage', 'wine_type', 'style',
        'grapes', 'allergens', 'description_es', 'description_en',
        'alcohol_content', 'serving_temp_min', 'serving_temp_max',
        'price_min', 'price_max', 'image_url', 'rating_average',
        'rating_count', 'is_featured', 'is_active', 'created_by',
    ];

    protected $casts = [
        'grapes' => 'array',
        'allergens' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'rating_average' => 'float',
        'rating_count' => 'integer',
        'alcohol_content' => 'float',
        'price_min' => 'float',
        'price_max' => 'float',
    ];

    public function winery(): BelongsTo
    {
        return $this->belongsTo(Winery::class, 'winery_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'wine_id');
    }

    public function aiProfiles(): HasMany
    {
        return $this->hasMany(WineAiProfile::class, 'wine_id');
    }

    public function cellarWines(): HasMany
    {
        return $this->hasMany(CellarWine::class, 'wine_id');
    }

    public function getWineryName(): ?string
    {
        return $this->winery?->name;
    }

    public function getRegion(): ?string
    {
        return $this->winery?->region;
    }

    public function getCountry(): ?string
    {
        return $this->winery?->country;
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }
}
