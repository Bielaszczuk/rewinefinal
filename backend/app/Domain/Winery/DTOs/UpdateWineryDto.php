<?php

namespace App\Domain\Winery\DTOs;

readonly class UpdateWineryDto
{
    public function __construct(
        public ?string $name = null,
        public ?string $country = null,
        public ?string $region = null,
        public ?string $subregion = null,
        public ?string $description = null,
        public ?string $websiteUrl = null,
        public ?string $contactEmail = null,
        public ?string $contactPhone = null,
        public ?string $address = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $logoUrl = null,
        public ?int $established = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'country' => $this->country,
            'region' => $this->region,
            'subregion' => $this->subregion,
            'description' => $this->description,
            'website_url' => $this->websiteUrl,
            'contact_email' => $this->contactEmail,
            'contact_phone' => $this->contactPhone,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'logo_url' => $this->logoUrl,
            'established' => $this->established,
        ], fn($value) => $value !== null);
    }
}
