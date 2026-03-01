<?php

namespace App\Domain\WineRoute\DTOs;

class UpdateWineRouteDto
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $country = null,
        public readonly ?string $region = null,
        public readonly ?string $subregion = null,
        public readonly ?string $estimatedDuration = null,
        public readonly ?int $estimatedDays = null,
        public readonly ?float $totalDistance = null,
        public readonly ?string $difficulty = null,
        public readonly ?string $imageUrl = null,
        public readonly ?string $mapEmbedUrl = null,
        public readonly ?string $status = null,
        public readonly ?array $recommendedWineTypes = null,
        public readonly ?array $stops = null,
        public readonly ?array $wineryIds = null,
    ) {}

    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) $data['name'] = $this->name;
        if ($this->description !== null) $data['description'] = $this->description;
        if ($this->country !== null) $data['country'] = $this->country;
        if ($this->region !== null) $data['region'] = $this->region;
        if ($this->subregion !== null) $data['subregion'] = $this->subregion;
        if ($this->estimatedDuration !== null) $data['estimated_duration'] = $this->estimatedDuration;
        if ($this->estimatedDays !== null) $data['estimated_days'] = $this->estimatedDays;
        if ($this->totalDistance !== null) $data['total_distance'] = $this->totalDistance;
        if ($this->difficulty !== null) $data['difficulty'] = $this->difficulty;
        if ($this->imageUrl !== null) $data['image_url'] = $this->imageUrl;
        if ($this->mapEmbedUrl !== null) $data['map_embed_url'] = $this->mapEmbedUrl;
        if ($this->status !== null) $data['status'] = $this->status;
        if ($this->recommendedWineTypes !== null) $data['recommended_wine_types'] = $this->recommendedWineTypes;
        if ($this->stops !== null) $data['stops'] = $this->stops;
        if ($this->wineryIds !== null) $data['winery_ids'] = $this->wineryIds;

        return $data;
    }
}

