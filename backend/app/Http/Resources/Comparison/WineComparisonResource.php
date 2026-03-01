<?php

namespace App\Http\Resources\Comparison;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WineComparisonResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'wineAId' => $data['wineAId'] ?? null,
            'wineAName' => $data['wineAName'] ?? null,
            'wineBId' => $data['wineBId'] ?? null,
            'wineBName' => $data['wineBName'] ?? null,
            'language' => $data['language'] ?? 'es-AR',
            'generatedAt' => $data['generatedAt'] ?? now()->toISOString(),
            'cached' => $data['cached'] ?? false,
            'summary' => $data['summary'] ?? null,
            'keyDifferences' => $data['keyDifferences'] ?? [],
            'similarities' => $data['similarities'] ?? [],
            'attributeComparison' => $data['attributeComparison'] ?? null,
            'bestFor' => $data['bestFor'] ?? null,
            'recommendation' => $data['recommendation'] ?? null,
            'comparisonContent' => [
                'similarities' => $data['similarities'] ?? [],
                'keyDifferences' => $data['keyDifferences'] ?? [],
                'differences' => $data['keyDifferences'] ?? [],
                'recommendation' => $data['recommendation'] ?? null,
                'summary' => $data['summary'] ?? null,
                'attributeComparison' => $data['attributeComparison'] ?? null,
                'bestFor' => $data['bestFor'] ?? null,
            ],
        ];
    }
}
