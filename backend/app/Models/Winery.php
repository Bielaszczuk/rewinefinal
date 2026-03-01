<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Winery extends Model
{
    use HasUuids;

    protected $table = 'wineries';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name', 'country', 'region', 'subregion', 'description',
        'address', 'latitude', 'longitude',
        'contact_email', 'contact_phone',
        'website_url', 'logo_url', 'established',
    ];

    public function wines(): HasMany
    {
        return $this->hasMany(Wine::class, 'winery_id');
    }

    public function routes(): BelongsToMany
    {
        return $this->belongsToMany(WineRoute::class, 'route_wineries', 'winery_id', 'route_id');
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }
}
