<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Inventory\Service\InventoryService;
use App\Models\Sale;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class UpdateInventoryJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public readonly Sale $sale,
    ) {
    }

    public function handle(InventoryService $inventoryService): void
    {
        try {
            DB::transaction(function () use ($inventoryService) {
                // Update sale status to processing
                $this->sale->update(['status' => 'processing']);

                // Load sale items with products
                $this->sale->load('items.product');

                // Process each sale item
                foreach ($this->sale->items as $item) {
                    // Check stock availability
                    if (! $inventoryService->hasAvailableStock($item->product_id, $item->quantity)) {
                        throw new \RuntimeException(
                            "Insufficient stock for product {$item->product->sku}. Required: {$item->quantity}, Available: ".$inventoryService->getCurrentStock($item->product_id)
                        );
                    }

                    // Create inventory exit entry
                    $inventoryService->createInventoryExit(
                        companyId: $this->sale->company_id,
                        productId: $item->product_id,
                        quantity: $item->quantity,
                        saleId: $this->sale->id
                    );
                }

                // Update sale status to completed
                $this->sale->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                Log::info("Sale {$this->sale->sale_number} completed successfully");
            });
        } catch (\Exception $e) {
            // Update sale status to failed
            $this->sale->update(['status' => 'failed']);

            Log::error("Failed to process sale {$this->sale->sale_number}: {$e->getMessage()}");

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Job failed for sale {$this->sale->sale_number}: {$exception->getMessage()}");

        $this->sale->update(['status' => 'failed']);
    }
}

