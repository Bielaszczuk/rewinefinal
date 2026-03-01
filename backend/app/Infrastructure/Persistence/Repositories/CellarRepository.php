<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Cellar\Contracts\CellarRepositoryInterface;
use App\Models\CellarWine;
use App\Models\Wine;
use Illuminate\Support\Collection;

class CellarRepository implements CellarRepositoryInterface
{
    public function findById(string $id): ?CellarWine
    {
        return CellarWine::with('wine.winery')->find($id);
    }

    public function findByIdOrFail(string $id): CellarWine
    {
        return CellarWine::with('wine.winery')->findOrFail($id);
    }

    public function findByUserAndId(string $userId, string $entryId): ?CellarWine
    {
        return CellarWine::with('wine.winery')
            ->where('id', $entryId)
            ->where('user_id', $userId)
            ->first();
    }

    public function findByUserAndIdOrFail(string $userId, string $entryId): CellarWine
    {
        return CellarWine::with('wine.winery')
            ->where('id', $entryId)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public function getByUser(string $userId): Collection
    {
        return CellarWine::with('wine.winery')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function addOrUpdateWine(string $userId, array $data): CellarWine
    {
        Wine::findOrFail($data['wineId']);

        $entry = CellarWine::updateOrCreate(
            ['user_id' => $userId, 'wine_id' => $data['wineId']],
            [
                'quantity'       => ($data['quantity'] ?? 1),
                'purchase_date'  => $data['purchaseDate'] ?? null,
                'purchase_price' => $data['purchasePrice'] ?? null,
                'location'       => $data['location'] ?? null,
                'notes'          => $data['notes'] ?? null,
            ]
        );

        return $entry->load('wine.winery');
    }

    public function update(string $userId, string $entryId, array $data): CellarWine
    {
        $entry = $this->findByUserAndIdOrFail($userId, $entryId);

        $entry->update([
            'quantity'       => $data['quantity'] ?? $entry->quantity,
            'purchase_date'  => $data['purchaseDate'] ?? $entry->purchase_date,
            'purchase_price' => $data['purchasePrice'] ?? $entry->purchase_price,
            'location'       => $data['location'] ?? $entry->location,
            'notes'          => $data['notes'] ?? $entry->notes,
        ]);

        return $entry->load('wine.winery');
    }

    public function delete(string $userId, string $entryId): bool
    {
        $entry = $this->findByUserAndIdOrFail($userId, $entryId);
        return $entry->delete();
    }

    public function getStatsByUser(string $userId): array
    {
        $entries = CellarWine::with('wine')
            ->where('user_id', $userId)
            ->get();

        $totalBottles = $entries->sum('quantity');
        $totalValue   = $entries->sum(fn($e) => ($e->purchase_price ?? $e->wine?->price_min ?? 0) * $e->quantity);

        $byType = $entries->groupBy(fn($e) => $e->wine?->wine_type ?? 'UNKNOWN')
            ->map(fn($g) => $g->sum('quantity'));

        return [
            'totalBottles' => $totalBottles,
            'totalValue'   => round($totalValue, 2),
            'byType'       => $byType,
            'totalWines'   => $entries->count(),
        ];
    }
}

