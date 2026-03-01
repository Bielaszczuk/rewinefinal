<?php

namespace App\Domain\Review\DTOs;

readonly class CreateReviewDto
{
    public function __construct(
        public string $wineId,
        public string $userId,
        public int $rating,
        public ?string $title = null,
        public ?string $comment = null,
        public bool $isVerified = false,
    ) {}

    public function toArray(): array
    {
        return [
            'wine_id' => $this->wineId,
            'user_id' => $this->userId,
            'rating' => $this->rating,
            'title' => $this->title,
            'comment' => $this->comment,
            'is_verified' => $this->isVerified,
            'likes_count' => 0,
        ];
    }
}
