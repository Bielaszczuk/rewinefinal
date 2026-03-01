<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasUuids;

    protected $table = 'events';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title', 'description', 'type', 'start_date', 'end_date',
        'location_name', 'location_address', 'location_city', 'location_region',
        'latitude', 'longitude', 'price', 'max_attendees', 'current_attendees',
        'status', 'image_url', 'organizer_id', 'organizer_type',
        'contact_email', 'contact_phone', 'website_url', 'map_embed_url',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'price' => 'float',
        'max_attendees' => 'integer',
        'current_attendees' => 'integer',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'PUBLISHED'
            && ($this->max_attendees === null || $this->current_attendees < $this->max_attendees)
            && $this->start_date->isFuture();
    }

    public function getAvailableSpots(): ?int
    {
        if ($this->max_attendees === null) {
            return null;
        }
        return max(0, $this->max_attendees - $this->current_attendees);
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }
}
