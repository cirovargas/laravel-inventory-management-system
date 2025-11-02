<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Sales\Repository\SaleRepositoryInterface;
use App\Models\Sale;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
            ->where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate])
//            ->with(['items.product'])
        ;

        if ($sku !== null) {
//            $query->whereHas('items.product', function ($q) use ($sku) {
//                $q->where('sku', $sku);
//            });
        }

        return $query->orderBy('sale_date', 'desc')->cursorPaginate($perPage);
    }

    public function getSalesMetrics(
        int $companyId,
        string $startDate,
        string $endDate,
        ?string $sku = null
    ): array {
        // select COUNT(*)                                                                                           as total_sales,
        //       SUM(total_amount)                                                                                  as total_amount,
        //       SUM(total_profit)                                                                                  as total_profit,
        //       SUM((SELECT SUM(quantity)
        //            FROM sale_items
        //            WHERE sale_items.sale_id = sales.id
        //              and company_id = 1))                                                                        as total_quantity
        //from "sales"
        //where "company_id" = 1
        //  and "status" = 'completed'
        //  and "sale_date" between '2024-01-01' and '2024-12-31'
        //  and exists (select *
        //              from "sale_items"
        //              where "sales"."id" = "sale_items"."sale_id"
        //                and exists (select *
        //                            from "products"
        //                            where "sale_items"."product_id" = "products"."id"
        //                              and "sku" = 'PROD-001'
        //                              and "company_id" = 1
        //                              and "products"."deleted_at" is null))
        //  and "sales"."deleted_at" is null

        $metrics = Sale::query()
            ->where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->selectRaw('COUNT(*) as total_sales')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->selectRaw('SUM(total_profit) as total_profit')
            ->selectRaw('SUM((SELECT SUM(quantity) FROM sale_items WHERE sale_items.sale_id = sales.id and company_id = '.$companyId.')) as total_quantity')

            ->first();

        return [
            'total_sales' => (int) ($metrics->total_sales ?? 0),
            'total_amount' => (float) ($metrics->total_amount ?? 0),
            'total_profit' => (float) ($metrics->total_profit ?? 0),
            'total_quantity' => (int) ($metrics->total_quantity ?? 0),
        ];
    }
}

