<?php

namespace App\Domain\WineRoute\DTOs;

class CreateWineRouteDto
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $country,
        public readonly ?string $region,
        public readonly ?string $subregion,
        public readonly ?string $estimatedDuration,
        public readonly ?int $estimatedDays,
        public readonly ?float $totalDistance,
        public readonly ?string $difficulty,
        public readonly ?string $imageUrl,
        public readonly ?string $mapEmbedUrl,
        public readonly ?string $status,
        public readonly ?array $recommendedWineTypes,
        public readonly ?string $createdBy,
        public readonly ?array $stops,
        public readonly ?array $wineryIds,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'country' => $this->country,
            'region' => $this->region,
            'subregion' => $this->subregion,
            'estimated_duration' => $this->estimatedDuration,
            'estimated_days' => $this->estimatedDays,
            'total_distance' => $this->totalDistance,
            'difficulty' => $this->difficulty ?? 'moderate',
            'image_url' => $this->imageUrl,
            'map_embed_url' => $this->mapEmbedUrl,
            'status' => $this->status ?? 'active',
            'recommended_wine_types' => $this->recommendedWineTypes ?? [],
            'created_by' => $this->createdBy,
            'stops' => $this->stops ?? [],
            'winery_ids' => $this->wineryIds ?? [],
        ];
    }
}

