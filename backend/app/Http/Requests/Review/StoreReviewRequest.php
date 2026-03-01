<?php

namespace App\Http\Requests\Review;

use App\Domain\Review\DTOs\CreateReviewDto;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'La calificación es requerida',
            'rating.min' => 'La calificación debe ser entre 1 y 5',
            'rating.max' => 'La calificación debe ser entre 1 y 5',
        ];
    }

    public function toDto(): CreateReviewDto
    {
        return new CreateReviewDto(
            wineId: $this->route('wineId'),
            userId: auth('api')->id(),
            rating: (int) $this->input('rating'),
            title: $this->input('title'),
            comment: $this->input('comment')
        );
    }
}
