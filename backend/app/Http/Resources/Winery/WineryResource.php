<?php

namespace App\Http\Resources\Winery;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WineryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->country,
            'region' => $this->region,
            'subregion' => $this->subregion,
            'description' => $this->description,
            'websiteUrl' => $this->website_url,
            'contactEmail' => $this->contact_email,
            'contactPhone' => $this->contact_phone,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'logoUrl' => $this->logo_url,
            'established' => $this->established,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'winesCount' => $this->when(
                $this->relationLoaded('wines'),
                fn() => $this->wines->count()
            ),
        ];
    }
}
