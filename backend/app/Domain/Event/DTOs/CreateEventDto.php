<?php

namespace App\Domain\Event\DTOs;

readonly class CreateEventDto
{
    public function __construct(
        public string $title,
        public string $type,
        public string $startDate,
        public string $endDate,
        public ?string $description = null,
        public ?string $locationName = null,
        public ?string $locationAddress = null,
        public ?string $locationCity = null,
        public ?string $locationRegion = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?float $price = null,
        public ?int $maxAttendees = null,
        public string $status = 'DRAFT',
        public ?string $imageUrl = null,
        public ?string $organizerId = null,
        public string $organizerType = 'PARTNER',
        public ?string $contactEmail = null,
        public ?string $contactPhone = null,
        public ?string $websiteUrl = null,
    ) {}

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'location_name' => $this->locationName,
            'location_address' => $this->locationAddress,
            'location_city' => $this->locationCity,
            'location_region' => $this->locationRegion,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'price' => $this->price,
            'max_attendees' => $this->maxAttendees,
            'current_attendees' => 0,
            'status' => $this->status,
            'image_url' => $this->imageUrl,
            'organizer_id' => $this->organizerId,
            'organizer_type' => $this->organizerType,
            'contact_email' => $this->contactEmail,
            'contact_phone' => $this->contactPhone,
            'website_url' => $this->websiteUrl,
        ];
    }
}
