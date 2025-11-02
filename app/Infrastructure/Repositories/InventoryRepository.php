<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Inventory\Repository\InventoryRepositoryInterface;
use App\Models\InventoryEntry;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class InventoryRepository implements InventoryRepositoryInterface
{
    public function createEntry(array $data): InventoryEntry
    {
        return InventoryEntry::query()->create($data);
    }

    public function getEntriesByProduct(int $productId): Collection
    {
        return InventoryEntry::query()
            ->where('product_id', $productId)
            ->orderBy('entry_date', 'desc')
            ->get();
    }

    public function getEntriesByCompany(int $companyId): Collection
    {
        return InventoryEntry::query()
            ->where('company_id', $companyId)
            ->with(['product'])
            ->orderBy('entry_date', 'desc')
            ->get();
    }

    public function getCurrentStock(int $productId): int
    {
        $result = InventoryEntry::query()
            ->where('product_id', $productId)
            ->selectRaw('SUM(CASE WHEN type = ? THEN quantity ELSE -quantity END) as stock', ['entry'])
            ->value('stock');

        return (int) ($result ?? 0);
    }

    public function getInventoryStatus(int $companyId): Collection
    {
        return Product::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->with(['inventoryEntries' => function ($query) {
                $query->select('product_id', 'type', 'quantity', 'unit_cost');
            }])
            ->get()
            ->map(function (Product $product) {
                $entries = $product->inventoryEntries;

                $currentStock = $entries->sum(function ($entry) {
                    return $entry->type === 'entry' ? $entry->quantity : -$entry->quantity;
                });

                $totalValue = $entries
                    ->where('type', 'entry')
                    ->sum(function ($entry) {
                        return $entry->quantity * $entry->unit_cost;
                    });

                $projectedProfit = $currentStock * ($product->sale_price - $product->cost_price);

                return [
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'current_stock' => $currentStock,
                    'cost_price' => $product->cost_price,
                    'sale_price' => $product->sale_price,
                    'total_value' => $totalValue,
                    'projected_profit' => $projectedProfit,
                ];
            });
    }

    public function getStaleInventoryRecords(int $companyId, int $daysOld = 90): Collection
    {
        $cutoffDate = now()->subDays($daysOld);

        return Product::query()
            ->where('company_id', $companyId)
            ->whereDoesntHave('inventoryEntries', function ($query) use ($cutoffDate) {
                $query->where('updated_at', '>=', $cutoffDate);
            })
            ->orWhereHas('inventoryEntries', function ($query) use ($cutoffDate) {
                $query->where('updated_at', '<', $cutoffDate)
                    ->whereRaw('updated_at = (SELECT MAX(updated_at) FROM inventory_entries WHERE product_id = products.id)');
            })
            ->get();
    }

    public function hasAvailableStock(int $productId, int $quantity): bool
    {
        $currentStock = $this->getCurrentStock($productId);

        return $currentStock >= $quantity;
    }
}

