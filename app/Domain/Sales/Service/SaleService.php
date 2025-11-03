<?php

declare(strict_types=1);

namespace App\Domain\Sales\Service;

use App\Domain\Inventory\Repository\ProductRepositoryInterface;
use App\Domain\Sales\DTO\CreateSaleData;
use App\Domain\Sales\Enum\SaleStatus;
use App\Domain\Sales\Repository\SaleRepositoryInterface;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;

final class SaleService
{
    public function __construct(
        private readonly SaleRepositoryInterface $saleRepository,
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    public function createSale(CreateSaleData $data): Sale
    {
        return DB::transaction(function () use ($data) {
            foreach ($data->items as $item) {
                $product = $this->productRepository->findById($item->productId);

                if ($product === null) {
                    throw new \InvalidArgumentException("Product {$item->productId} not found");
                }

                if ($product->company_id !== $data->companyId) {
                    throw new \InvalidArgumentException("Product {$item->productId} does not belong to this company");
                }
            }

            // Create the sale
            $sale = $this->saleRepository->create([
                'company_id' => $data->companyId,
                'sale_number' => $this->generateSaleNumber(),
                'total_amount' => 0,
                'total_cost' => 0,
                'total_profit' => 0,
                'status' => SaleStatus::PENDING,
                'sale_date' => now(),
                'notes' => $data->notes,
            ]);

            $totalAmount = 0;
            $totalCost = 0;

            foreach ($data->items as $item) {
                $product = $this->productRepository->findById($item->productId);

                $subtotal = $item->quantity * $product->sale_price;
                $costTotal = $item->quantity * $product->cost_price;
                $profit = $subtotal - $costTotal;

                SaleItem::query()->create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $product->sale_price,
                    'unit_cost' => $product->cost_price,
                    'subtotal' => $subtotal,
                    'cost_total' => $costTotal,
                    'profit' => $profit,
                ]);

                $totalAmount += $subtotal;
                $totalCost += $costTotal;
            }

            $sale = $this->saleRepository->update($sale, [
                'total_amount' => $totalAmount,
                'total_cost' => $totalCost,
                'total_profit' => $totalAmount - $totalCost,
            ]);

            return $sale;
        });
    }

    public function getSaleById(int $id): ?Sale
    {
        return $this->saleRepository->findByIdWithItems($id);
    }

    public function getSalesReport(
        int $companyId,
        string $startDate,
        string $endDate,
        ?string $sku = null,
        int $perPage = 15
    ): CursorPaginator {
        return $this->saleRepository->getSalesReport($companyId, $startDate, $endDate, $sku, $perPage);
    }

    public function getSalesMetrics(
        int $companyId,
        string $startDate,
        string $endDate,
        ?string $sku = null
    ): array {
        return $this->saleRepository->getSalesMetrics($companyId, $startDate, $endDate, $sku);
    }

    private function generateSaleNumber(): string
    {
        return 'SALE-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
    }
}
