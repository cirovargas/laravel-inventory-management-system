<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Inventory\Service\InventoryService;
use App\Domain\Sales\DTO\CreateSaleData;
use App\Domain\Sales\Enum\SaleStatus;
use App\Domain\Sales\Service\SaleService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class ProcessSaleJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public readonly CreateSaleData $saleData,
        public readonly string $trackingId,
    ) {}

    public function handle(SaleService $saleService, InventoryService $inventoryService): void
    {
        try {
            DB::transaction(function () use ($saleService, $inventoryService) {
                Log::info("Processing sale with tracking ID: {$this->trackingId}");

                // Create the sale
                $sale = $saleService->createSale($this->saleData);

                Log::info("Sale {$sale->sale_number} created with ID: {$sale->id}");

                // Update status to processing
                $sale->update(['status' => SaleStatus::PROCESSING]);

                // Load items with products for inventory processing
                $sale->load('items.product');

                // Process inventory updates for each item
                foreach ($sale->items as $item) {
                    if (! $inventoryService->hasAvailableStock($item->product_id, $item->quantity)) {
                        throw new \RuntimeException(
                            "Insufficient stock for product {$item->product->sku}. Required: {$item->quantity}, ".
                            "Available: ".$inventoryService->getCurrentStock($item->product_id)
                        );
                    }

                    $inventoryService->createInventoryExit(
                        companyId: $sale->company_id,
                        productId: $item->product_id,
                        quantity: $item->quantity,
                        saleId: $sale->id
                    );
                }

                // Mark sale as completed
                $sale->update([
                    'status' => SaleStatus::COMPLETED,
                    'completed_at' => now(),
                ]);

                Log::info("Sale {$sale->sale_number} (tracking ID: {$this->trackingId}) completed successfully");
            });
        } catch (\Exception $e) {
            Log::error("Failed to process sale with tracking ID {$this->trackingId}: {$e->getMessage()}");

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Job failed for sale tracking ID {$this->trackingId}: {$exception->getMessage()}");
    }
}

