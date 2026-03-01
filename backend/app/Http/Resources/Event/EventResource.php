<?php

namespace App\Http\Resources\Event;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'startDate' => $this->start_date?->toISOString(),
            'endDate' => $this->end_date?->toISOString(),
            'locationName' => $this->location_name,
            'locationAddress' => $this->location_address,
            'locationCity' => $this->location_city,
            'locationRegion' => $this->location_region,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'price' => $this->price,
            'maxAttendees' => $this->max_attendees,
            'currentAttendees' => $this->current_attendees ?? 0,
            'status' => $this->status,
            'imageUrl' => $this->image_url,
            'organizerId' => $this->organizer_id,
            'organizerType' => $this->organizer_type,
            'contactEmail' => $this->contact_email,
            'contactPhone' => $this->contact_phone,
            'websiteUrl' => $this->website_url,
            'mapEmbedUrl' => $this->map_embed_url,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'organizer' => $this->whenLoaded('organizer', fn() => [
                'id' => $this->organizer->id,
                'name' => $this->organizer->name,
                'email' => $this->organizer->email,
            ]),
        ];
    }
}
