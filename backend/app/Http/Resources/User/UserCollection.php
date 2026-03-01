<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public $collects = UserResource::class;

    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'items' => $this->collection,
            'content' => $this->collection,
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
