<?php

namespace App\Http\Resources\Wine;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'vintage' => $this->vintage,
            'wineType' => $this->wine_type,
            'style' => $this->style,
            'grapes' => $this->grapes ?? [],
            'allergens' => $this->allergens ?? [],
            'descriptionEs' => $this->description_es,
            'descriptionEn' => $this->description_en,
            'alcoholContent' => $this->alcohol_content,
            'servingTempMin' => $this->serving_temp_min,
            'servingTempMax' => $this->serving_temp_max,
            'priceMin' => $this->price_min,
            'priceMax' => $this->price_max,
            'imageUrl' => $this->image_url,
            'ratingAverage' => $this->rating_average,
            'ratingCount' => $this->rating_count,
            'isFeatured' => $this->is_featured,
            'isActive' => $this->is_active,
            'wineryId' => $this->winery_id,
            'wineryName' => $this->winery?->name,
            'region' => $this->winery?->region,
            'country' => $this->winery?->country,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'winery' => $this->whenLoaded('winery', fn() => [
                'id' => $this->winery->id,
                'name' => $this->winery->name,
                'country' => $this->winery->country,
                'region' => $this->winery->region,
                'subregion' => $this->winery->subregion,
                'description' => $this->winery->description,
                'websiteUrl' => $this->winery->website_url,
                'contactEmail' => $this->winery->contact_email,
                'contactPhone' => $this->winery->contact_phone,
                'address' => $this->winery->address,
                'latitude' => $this->winery->latitude,
                'longitude' => $this->winery->longitude,
                'logoUrl' => $this->winery->logo_url,
                'established' => $this->winery->established,
            ]),
        ];
    }
}
