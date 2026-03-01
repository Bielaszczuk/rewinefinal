<?php

namespace App\Http\Requests\Review;

use App\Domain\Review\DTOs\UpdateReviewDto;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    public function rules(): array
    {
        return [
            'rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function toDto(): UpdateReviewDto
    {
        return new UpdateReviewDto(
            rating: $this->input('rating'),
            title: $this->input('title'),
            comment: $this->input('comment')
        );
    }
}
