<?php

namespace App\Domain\Wine\DTOs;

readonly class CreateWineDto
{
    public function __construct(
        public string $name,
        public string $wineryId,
        public string $wineType,
        public ?int $vintage = null,
        public ?string $descriptionEs = null,
        public ?string $descriptionEn = null,
        public ?float $alcoholContent = null,
        public ?float $servingTempMin = null,
        public ?float $servingTempMax = null,
        public ?float $priceMin = null,
        public ?float $priceMax = null,
        public ?string $imageUrl = null,
        public array $grapes = [],
        public array $allergens = [],
        public bool $isFeatured = false,
        public bool $isActive = true,
        public ?string $style = null,
    ) {}

    /**
     * Convert DTO to array for Eloquent.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'winery_id' => $this->wineryId,
            'wine_type' => $this->wineType,
            'vintage' => $this->vintage,
            'description_es' => $this->descriptionEs,
            'description_en' => $this->descriptionEn,
            'alcohol_content' => $this->alcoholContent,
            'serving_temp_min' => $this->servingTempMin,
            'serving_temp_max' => $this->servingTempMax,
            'price_min' => $this->priceMin,
            'price_max' => $this->priceMax,
            'image_url' => $this->imageUrl,
            'grapes' => $this->grapes,
            'allergens' => $this->allergens,
            'is_featured' => $this->isFeatured,
            'is_active' => $this->isActive,
            'style' => $this->style,
        ];
    }
}
