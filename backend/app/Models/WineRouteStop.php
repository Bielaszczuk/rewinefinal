<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WineRouteStop extends Model
{
    use HasUuids;

    protected $table = 'wine_route_stops';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'wine_route_id', 'name', 'description', 'type',
        'address', 'latitude', 'longitude', 'stop_order', 'estimated_duration',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'stop_order' => 'integer',
        'estimated_duration' => 'integer',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(WineRoute::class, 'wine_route_id');
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }
}
