<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Service;

use App\Domain\Inventory\DTO\InventoryEntryData;
use App\Domain\Inventory\Enum\InventoryType;
use App\Domain\Inventory\Repository\InventoryRepositoryInterface;
use App\Domain\Inventory\Repository\ProductRepositoryInterface;
use App\Models\InventoryEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class InventoryService
{
    public function __construct(
        private readonly InventoryRepositoryInterface $inventoryRepository,
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    public function registerEntry(InventoryEntryData $data): InventoryEntry
    {
        return DB::transaction(function () use ($data) {
            $product = $this->productRepository->findById($data->productId);

            if ($product === null) {
                throw new \InvalidArgumentException('Product not found');
            }

            if ($product->company_id !== $data->companyId) {
                throw new \InvalidArgumentException('Product does not belong to this company');
            }

            $entry = $this->inventoryRepository->createEntry($data->toArray());

            // Invalidate cache for this company's inventory
            Cache::forget("inventory_status_{$data->companyId}");

            return $entry;
        });
    }

    public function getInventoryStatus(int $companyId): Collection
    {
        return Cache::remember(
            "inventory_status_{$companyId}",
            now()->addMinutes(5),
            fn () => $this->inventoryRepository->getInventoryStatus($companyId)
        );
    }

    public function getCurrentStock(int $productId): int
    {
        return $this->inventoryRepository->getCurrentStock($productId);
    }

    public function hasAvailableStock(int $productId, int $quantity): bool
    {
        return $this->inventoryRepository->hasAvailableStock($productId, $quantity);
    }

    public function getStaleInventoryRecords(int $companyId, int $daysOld = 90): Collection
    {
        return $this->inventoryRepository->getStaleInventoryRecords($companyId, $daysOld);
    }

    public function createInventoryExit(int $companyId, int $productId, int $quantity, ?int $saleId = null): InventoryEntry
    {
        return DB::transaction(function () use ($companyId, $productId, $quantity, $saleId) {
            $product = $this->productRepository->findById($productId);

            if ($product === null) {
                throw new \InvalidArgumentException('Product not found');
            }

            if ($product->company_id !== $companyId) {
                throw new \InvalidArgumentException('Product does not belong to this company');
            }

            if (! $this->hasAvailableStock($productId, $quantity)) {
                throw new \RuntimeException('Insufficient stock available');
            }

            $entry = $this->inventoryRepository->createEntry([
                'company_id' => $companyId,
                'product_id' => $productId,
                'type' => InventoryType::EXIT,
                'quantity' => $quantity,
                'unit_cost' => $product->cost_price,
                'sale_id' => $saleId,
                'entry_date' => now(),
            ]);

            // Invalidate cache for this company's inventory
            Cache::forget("inventory_status_{$companyId}");

            return $entry;
        });
    }
}
