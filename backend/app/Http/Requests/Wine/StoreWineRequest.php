<?php

namespace App\Http\Requests\Wine;

use App\Domain\Wine\DTOs\CreateWineDto;
use Illuminate\Foundation\Http\FormRequest;

class StoreWineRequest extends FormRequest
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
            'wineryId' => ['required', 'uuid', 'exists:wineries,id'],
            'wineType' => ['required', 'string', 'in:RED,WHITE,ROSE,SPARKLING,DESSERT,FORTIFIED'],
            'vintage' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'descriptionEs' => ['nullable', 'string', 'max:2000'],
            'descriptionEn' => ['nullable', 'string', 'max:2000'],
            'alcoholContent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'servingTempMin' => ['nullable', 'numeric', 'min:-20', 'max:30'],
            'servingTempMax' => ['nullable', 'numeric', 'min:-20', 'max:30', 'gte:servingTempMin'],
            'priceMin' => ['nullable', 'numeric', 'min:0'],
            'priceMax' => ['nullable', 'numeric', 'min:0', 'gte:priceMin'],
            'imageUrl' => ['nullable', 'url', 'max:500'],
            'grapes' => ['nullable', 'array'],
            'grapes.*' => ['string', 'max:100'],
            'allergens' => ['nullable', 'array'],
            'allergens.*' => ['string', 'max:100'],
            'isFeatured' => ['nullable', 'boolean'],
            'isActive' => ['nullable', 'boolean'],
            'style' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del vino es requerido',
            'wineryId.required' => 'La bodega es requerida',
            'wineryId.exists' => 'La bodega seleccionada no existe',
            'wineType.required' => 'El tipo de vino es requerido',
            'wineType.in' => 'El tipo de vino debe ser: RED, WHITE, ROSE, SPARKLING, DESSERT o FORTIFIED',
            'vintage.min' => 'El año debe ser mayor a 1900',
            'vintage.max' => 'El año no puede ser futuro',
            'servingTempMax.gte' => 'La temperatura máxima debe ser mayor o igual a la mínima',
            'priceMax.gte' => 'El precio máximo debe ser mayor o igual al mínimo',
        ];
    }

    /**
     * Convert request to DTO.
     */
    public function toDto(): CreateWineDto
    {
        return new CreateWineDto(
            name: $this->input('name'),
            wineryId: $this->input('wineryId'),
            wineType: $this->input('wineType'),
            vintage: $this->input('vintage'),
            descriptionEs: $this->input('descriptionEs'),
            descriptionEn: $this->input('descriptionEn'),
            alcoholContent: $this->input('alcoholContent'),
            servingTempMin: $this->input('servingTempMin'),
            servingTempMax: $this->input('servingTempMax'),
            priceMin: $this->input('priceMin'),
            priceMax: $this->input('priceMax'),
            imageUrl: $this->input('imageUrl'),
            grapes: $this->input('grapes', []),
            allergens: $this->input('allergens', []),
            isFeatured: $this->boolean('isFeatured', false),
            isActive: $this->boolean('isActive', true),
            style: $this->input('style')
        );
    }
}
