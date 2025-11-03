<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Sales\Enum\SaleStatus;
use App\Domain\Sales\Repository\SaleRepositoryInterface;
use App\Models\Sale;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class SaleRepository implements SaleRepositoryInterface
{
    public function findById(int $id): ?Sale
    {
        return Sale::query()->find($id);
    }

    public function findByIdWithItems(int $id): ?Sale
    {
        return Sale::query()
            ->with(['items.product'])
            ->find($id);
    }

    public function create(array $data): Sale
    {
        return Sale::query()->create($data);
    }

    public function update(Sale $sale, array $data): Sale
    {
        $sale->update($data);

        return $sale->fresh();
    }

    public function delete(Sale $sale): bool
    {
        return $sale->delete();
    }

    public function getSalesByCompany(int $companyId): Collection
    {
        return Sale::query()
            ->where('company_id', $companyId)
            ->with(['items.product'])
            ->orderBy('sale_date', 'desc')
            ->get();
    }

    public function getSalesReport(
        int $companyId,
        string $startDate,
        string $endDate,
        ?string $sku = null,
        int $perPage = 15
    ): CursorPaginator {
        $query = Sale::query()
            ->where('sales.company_id', $companyId)
            ->where('sales.status', SaleStatus::COMPLETED)
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->whereNull('sales.deleted_at')
            ->when($sku, function ($query, $sku) use ($companyId, $startDate, $endDate) {
                $query->whereExists(function ($q) use ($companyId, $sku, $startDate, $endDate) {
                    $q->from('sale_items')
                        ->whereColumn('sales.id', 'sale_items.sale_id')
                        ->where('sale_items.company_id', $companyId)
                        ->whereBetween('sale_date', [$startDate, $endDate])
                        ->whereExists(function ($q2) use ($companyId, $sku) {
                            $q2->from('products')
                                ->whereColumn('sale_items.product_id', 'products.id')
                                ->where('products.sku', $sku)
                                ->where('products.company_id', $companyId)
                                ->whereNull('products.deleted_at');
                        });
                });
            })
            ->orderByDesc('sales.sale_date');

        return $query->cursorPaginate($perPage);
    }

    public function getSalesMetrics(
        int $companyId,
        string $startDate,
        string $endDate,
        ?string $sku = null
    ): array {
        $qtyBySale = DB::table('sale_items')
            ->select('sale_id', DB::raw('SUM(quantity) AS qty'))
            ->where('company_id', $companyId)
            ->groupBy('sale_id');

        $metrics = DB::table('sales')
            ->leftJoinSub($qtyBySale, 'si', fn ($j) => $j->on('si.sale_id', '=', 'sales.id'))
            ->where('sales.company_id', $companyId)
            ->where('sales.status', SaleStatus::COMPLETED)
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->whereNull('sales.deleted_at')
            ->when($sku, function ($query, $sku) use ($companyId, $startDate, $endDate) {
                $query->whereExists(function ($q) use ($sku, $companyId, $startDate, $endDate) {
                    $q->from('sale_items')
                        ->where('company_id', $companyId)
                        ->whereBetween('sale_date', [$startDate, $endDate])
                        ->whereColumn('sales.id', 'sale_items.sale_id')
                        ->whereExists(function ($q2) use ($sku, $companyId) {
                            $q2->from('products')
                                ->whereColumn('sale_items.product_id', 'products.id')
                                ->where('sku', $sku)
                                ->where('products.company_id', $companyId)
                                ->whereNull('products.deleted_at');
                        });
                });
            })->selectRaw('COUNT(*) AS total_sales,
                 SUM(sales.total_amount) AS total_amount,
                 SUM(sales.total_profit) AS total_profit,
                 COALESCE(SUM(si.qty), 0) AS total_quantity')->first();

        return [
            'total_sales' => (int) ($metrics->total_sales ?? 0),
            'total_amount' => (float) ($metrics->total_amount ?? 0),
            'total_profit' => (float) ($metrics->total_profit ?? 0),
            'total_quantity' => (int) ($metrics->total_quantity ?? 0),
        ];
    }
}
