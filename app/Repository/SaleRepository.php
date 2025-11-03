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

        $candidateSales = DB::table('sale_items as si')
            ->join('products as p', 'p.id', '=', 'si.product_id')
            ->where('p.company_id', $companyId)
            ->when($sku, function ($query, $sku) {
                $query->where('p.sku', $sku);
            })
            ->whereNull('p.deleted_at')
            ->where('si.company_id', $companyId)
            ->where('si.sale_date', '>=', $startDate)
            ->where('si.sale_date', '<', $endDate)
            ->distinct()
            ->select('si.sale_id');

        $aggItems = DB::table('sale_items as si')
            ->select('si.sale_id', DB::raw('SUM(si.quantity) AS qty'))
            ->where('si.company_id', $companyId)
            ->whereIn('si.sale_id', $candidateSales)
            ->groupBy('si.sale_id');

        $metrics = DB::table('sales as s')
            ->joinSub($candidateSales, 'cs', function ($j) {
                $j->on('cs.sale_id', '=', 's.id');
            })
            ->leftJoinSub($aggItems, 'a', function ($j) {
                $j->on('a.sale_id', '=', 's.id');
            })
            ->where('s.company_id', $companyId)
            ->where('s.status', 'completed')
            ->whereNull('s.deleted_at')
            ->where('s.sale_date', '>=', $startDate)
            ->where('s.sale_date', '<', $endDate)
            ->selectRaw('COUNT(*) AS total_sales,
                 SUM(s.total_amount) AS total_amount,
                 SUM(s.total_profit) AS total_profit,
                 COALESCE(SUM(a.qty), 0) AS total_quantity')
            ->first();

        return [
            'total_sales' => (int) ($metrics->total_sales ?? 0),
            'total_amount' => (float) ($metrics->total_amount ?? 0),
            'total_profit' => (float) ($metrics->total_profit ?? 0),
            'total_quantity' => (int) ($metrics->total_quantity ?? 0),
        ];
    }
}
