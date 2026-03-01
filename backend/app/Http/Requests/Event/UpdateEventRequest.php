<?php

namespace App\Http\Requests\Event;

use App\Domain\Event\DTOs\UpdateEventDto;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth('api')->user();
        return $user && $user->hasAnyRole(['ROLE_ADMIN', 'ROLE_MODERATOR', 'ROLE_PARTNER']);
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['sometimes', 'string', 'in:TASTING,TOUR,FESTIVAL,CLASS,PAIRING,DINNER,OTHER'],
            'startDate' => ['sometimes', 'date'],
            'endDate' => ['sometimes', 'date'],
            'locationName' => ['nullable', 'string', 'max:255'],
            'locationAddress' => ['nullable', 'string', 'max:500'],
            'locationCity' => ['nullable', 'string', 'max:100'],
            'locationRegion' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'min:-90', 'max:90'],
            'longitude' => ['nullable', 'numeric', 'min:-180', 'max:180'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'maxAttendees' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:DRAFT,PUBLISHED,CANCELLED,COMPLETED'],
            'imageUrl' => ['nullable', 'url', 'max:500'],
            'organizerType' => ['nullable', 'string', 'in:PARTNER,WINERY,ADMIN,INDIVIDUAL'],
            'contactEmail' => ['nullable', 'email', 'max:255'],
            'contactPhone' => ['nullable', 'string', 'max:50'],
            'websiteUrl' => ['nullable', 'url', 'max:500'],
        ];
    }

    public function toDto(): UpdateEventDto
    {
        return new UpdateEventDto(
            title: $this->input('title'),
            description: $this->input('description'),
            type: $this->input('type'),
            startDate: $this->input('startDate'),
            endDate: $this->input('endDate'),
            locationName: $this->input('locationName'),
            locationAddress: $this->input('locationAddress'),
            locationCity: $this->input('locationCity'),
            locationRegion: $this->input('locationRegion'),
            latitude: $this->input('latitude'),
            longitude: $this->input('longitude'),
            price: $this->input('price'),
            maxAttendees: $this->input('maxAttendees'),
            status: $this->input('status'),
            imageUrl: $this->input('imageUrl'),
            organizerType: $this->input('organizerType'),
            contactEmail: $this->input('contactEmail'),
            contactPhone: $this->input('contactPhone'),
            websiteUrl: $this->input('websiteUrl')
        );
    }
}
