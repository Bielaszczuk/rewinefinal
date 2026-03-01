<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ReviewCollection extends ResourceCollection
{
    public $collects = ReviewResource::class;

    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $paginator = $this->resource;

        return [
            'items' => $this->collection,
            'content' => $this->collection,
            'pageNumber' => $paginator->currentPage() - 1,
            'pageSize' => $paginator->perPage(),
            'totalItems' => $paginator->total(),
            'totalPages' => $paginator->lastPage(),
            'first' => $paginator->currentPage() === 1,
            'last' => !$paginator->hasMorePages(),
            'hasNext' => $paginator->hasMorePages(),
            'hasPrevious' => $paginator->currentPage() > 1,
        ];
    }
}
