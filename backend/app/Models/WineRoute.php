<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WineRoute extends Model
{
    use HasUuids;

    protected $table = 'wine_routes';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name', 'description', 'country', 'region', 'subregion',
        'estimated_duration', 'estimated_days', 'total_distance',
        'difficulty', 'image_url', 'status', 'recommended_wine_types',
        'created_by', 'map_embed_url',
    ];

    protected $casts = [
        'recommended_wine_types' => 'array',
        'total_distance' => 'float',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stops(): HasMany
    {
        return $this->hasMany(WineRouteStop::class, 'wine_route_id')->orderBy('stop_order');
    }

    public function wineries(): BelongsToMany
    {
        return $this->belongsToMany(Winery::class, 'route_wineries', 'route_id', 'winery_id');
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }
}
