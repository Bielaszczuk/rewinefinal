<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PageResponse
{
    public static function from(LengthAwarePaginator $paginator, callable $transform = null): array
    {
        $items = $transform
            ? $paginator->getCollection()->map($transform)->values()->all()
            : $paginator->items();

        $pageNumber = $paginator->currentPage() - 1;
        $pageSize   = $paginator->perPage();
        $totalItems = $paginator->total();
        $totalPages = $paginator->lastPage();

        return [
            'items'       => $items,
            'pageNumber'  => $pageNumber,
            'pageSize'    => $pageSize,
            'totalItems'  => $totalItems,
            'totalPages'  => $totalPages,
            'first'       => $paginator->onFirstPage(),
            'last'        => !$paginator->hasMorePages(),
            'hasNext'     => $paginator->hasMorePages(),
            'hasPrevious' => $pageNumber > 0,
            'content'      => $items,
            'page'         => $pageNumber,
            'size'         => $pageSize,
            'totalElements' => $totalItems,
            'empty'        => count($items) === 0,
        ];
    }

    public static function fromArray(array $items, int $total, int $page, int $size): array
    {
        $totalPages = $size > 0 ? (int) ceil($total / $size) : 0;
        return [
            'items'       => $items,
            'pageNumber'  => $page,
            'pageSize'    => $size,
            'totalItems'  => $total,
            'totalPages'  => $totalPages,
            'first'       => $page === 0,
            'last'        => $page >= $totalPages - 1,
            'hasNext'     => $page < $totalPages - 1,
            'hasPrevious' => $page > 0,
            'content'     => $items,
            'page'        => $page,
            'size'        => $size,
            'totalElements' => $total,
            'empty'       => count($items) === 0,
        ];
    }
}
