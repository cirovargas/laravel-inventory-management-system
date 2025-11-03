<?php

declare(strict_types=1);

namespace App\Domain\Sales\Repository;

use App\Models\Sale;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Collection;

interface SaleRepositoryInterface
{
    public function findById(int $id): ?Sale;

    public function findByIdWithItems(int $id): ?Sale;

    public function create(array $data): Sale;

    public function update(Sale $sale, array $data): Sale;

    public function delete(Sale $sale): bool;

    public function getSalesByCompany(int $companyId): Collection;

    public function getSalesReport(
        int $companyId,
        string $startDate,
        string $endDate,
        ?string $sku = null,
        int $perPage = 15
    ): CursorPaginator;

    public function getSalesMetrics(
        int $companyId,
        string $startDate,
        string $endDate,
        ?string $sku = null
    ): array;
}
