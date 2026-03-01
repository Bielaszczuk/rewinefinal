<?php

namespace App\Domain\Review\DTOs;

readonly class UpdateReviewDto
{
    public function __construct(
        public ?int $rating = null,
        public ?string $title = null,
        public ?string $comment = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'rating' => $this->rating,
            'title' => $this->title,
            'comment' => $this->comment,
        ], fn($value) => $value !== null);
    }
}
