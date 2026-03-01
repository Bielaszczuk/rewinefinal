<?php

namespace App\Http\Requests\Winery;

use App\Domain\Winery\DTOs\CreateWineryDto;
use Illuminate\Foundation\Http\FormRequest;

class StoreWineryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth('api')->user();
        return $user && $user->hasAnyRole(['ROLE_ADMIN', 'ROLE_MODERATOR']);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:100'],
            'subregion' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'websiteUrl' => ['nullable', 'url', 'max:500'],
            'contactEmail' => ['nullable', 'email', 'max:255'],
            'contactPhone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'min:-90', 'max:90'],
            'longitude' => ['nullable', 'numeric', 'min:-180', 'max:180'],
            'logoUrl' => ['nullable', 'url', 'max:500'],
            'established' => ['nullable', 'integer', 'min:1000', 'max:' . date('Y')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la bodega es requerido',
            'country.required' => 'El país es requerido',
            'websiteUrl.url' => 'La URL del sitio web debe ser válida',
            'contactEmail.email' => 'El email debe ser válido',
            'latitude.min' => 'La latitud debe estar entre -90 y 90',
            'latitude.max' => 'La latitud debe estar entre -90 y 90',
            'longitude.min' => 'La longitud debe estar entre -180 y 180',
            'longitude.max' => 'La longitud debe estar entre -180 y 180',
        ];
    }

    public function toDto(): CreateWineryDto
    {
        return new CreateWineryDto(
            name: $this->input('name'),
            country: $this->input('country'),
            region: $this->input('region'),
            subregion: $this->input('subregion'),
            description: $this->input('description'),
            websiteUrl: $this->input('websiteUrl'),
            contactEmail: $this->input('contactEmail'),
            contactPhone: $this->input('contactPhone'),
            address: $this->input('address'),
            latitude: $this->input('latitude'),
            longitude: $this->input('longitude'),
            logoUrl: $this->input('logoUrl'),
            established: $this->input('established')
        );
    }
}
