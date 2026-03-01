<?php

namespace App\Http\Requests\Wine;

use App\Domain\Wine\DTOs\UpdateWineDto;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWineRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth('api')->user();
        return $user && $user->hasAnyRole(['ROLE_ADMIN', 'ROLE_MODERATOR']);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'wineryId' => ['sometimes', 'uuid', 'exists:wineries,id'],
            'wineType' => ['sometimes', 'string', 'in:RED,WHITE,ROSE,SPARKLING,DESSERT,FORTIFIED'],
            'vintage' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'descriptionEs' => ['nullable', 'string', 'max:2000'],
            'descriptionEn' => ['nullable', 'string', 'max:2000'],
            'alcoholContent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'servingTempMin' => ['nullable', 'numeric', 'min:-20', 'max:30'],
            'servingTempMax' => ['nullable', 'numeric', 'min:-20', 'max:30'],
            'priceMin' => ['nullable', 'numeric', 'min:0'],
            'priceMax' => ['nullable', 'numeric', 'min:0'],
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

    /**
     * Convert request to DTO.
     */
    public function toDto(): UpdateWineDto
    {
        return new UpdateWineDto(
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
            grapes: $this->input('grapes'),
            allergens: $this->input('allergens'),
            isFeatured: $this->has('isFeatured') ? $this->boolean('isFeatured') : null,
            isActive: $this->has('isActive') ? $this->boolean('isActive') : null,
            style: $this->input('style')
        );
    }
}
