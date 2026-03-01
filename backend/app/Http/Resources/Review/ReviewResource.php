<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentUserId = auth()->id();

        return [
            'id' => $this->id,
            'wineId' => $this->wine_id,
            'rating' => $this->rating,
            'title' => $this->title,
            'comment' => $this->comment,
            'likesCount' => $this->likes_count ?? 0,
            'commentsCount' => 0,
            'likedByCurrentUser' => false,
            'isVerified' => $this->is_verified ?? false,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'reviewer' => $this->when($this->relationLoaded('user') && $this->user, fn() => [
                'id' => $this->user->id,
                'username' => $this->user->username ?? 'Usuario',
                'displayName' => $this->user->name ?? $this->user->username ?? 'Usuario',
                'avatarUrl' => $this->user->avatar_url,
                'totalReviews' => 0,
            ]),
            'wine' => $this->when($this->relationLoaded('wine') && $this->wine, fn() => [
                'id' => $this->wine->id,
                'name' => $this->wine->name,
                'vintage' => $this->wine->vintage,
            ]),
        ];
    }
}
