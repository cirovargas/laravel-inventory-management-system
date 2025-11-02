<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Repository;

use App\Models\InventoryEntry;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface InventoryRepositoryInterface
{
    public function createEntry(array $data): InventoryEntry;

    public function getEntriesByProduct(int $productId): Collection;

    public function getEntriesByCompany(int $companyId): Collection;

    public function getCurrentStock(int $productId): int;

    public function getInventoryStatus(int $companyId): Collection;

    public function getStaleInventoryRecords(int $companyId, int $daysOld = 90): Collection;

    public function hasAvailableStock(int $productId, int $quantity): bool;
}

