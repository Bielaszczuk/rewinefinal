<?php

namespace App\Domain\Wine\DTOs;

readonly class UpdateWineDto
{
    public function __construct(
        public ?string $name = null,
        public ?string $wineryId = null,
        public ?string $wineType = null,
        public ?int $vintage = null,
        public ?string $descriptionEs = null,
        public ?string $descriptionEn = null,
        public ?float $alcoholContent = null,
        public ?float $servingTempMin = null,
        public ?float $servingTempMax = null,
        public ?float $priceMin = null,
        public ?float $priceMax = null,
        public ?string $imageUrl = null,
        public ?array $grapes = null,
        public ?array $allergens = null,
        public ?bool $isFeatured = null,
        public ?bool $isActive = null,
        public ?string $style = null,
    ) {}

    /**
     * Convert DTO to array for Eloquent, excluding null values.
     */
    public function toArray(): array
    {
        return array_filter([
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
        ], fn($value) => $value !== null);
    }
}
