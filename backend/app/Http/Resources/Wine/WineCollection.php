<?php

namespace App\Http\Resources\Wine;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class WineCollection extends ResourceCollection
{
    public $collects = WineResource::class;

    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $items = $this->collection->map(fn($item) => $item->toArray($request))->values();

        return [
            'items' => $items,
            'content' => $items,
            'pageNumber' => $this->currentPage() - 1,
            'pageSize' => $this->perPage(),
            'totalItems' => $this->total(),
            'totalPages' => $this->lastPage(),
            'first' => $this->currentPage() === 1,
            'last' => !$this->hasMorePages(),
            'hasNext' => $this->hasMorePages(),
            'hasPrevious' => $this->currentPage() > 1,
        ];
    }
}
