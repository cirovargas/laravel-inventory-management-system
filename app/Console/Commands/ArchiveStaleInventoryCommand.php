<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Inventory\Service\InventoryService;
use App\Models\Company;
use Illuminate\Console\Command;

final class ArchiveStaleInventoryCommand extends Command
{
    protected $signature = 'inventory:archive-stale {--days=90 : Number of days to consider inventory as stale}';

    protected $description = 'Archive or flag inventory records with no updates in the specified number of days';

    public function handle(InventoryService $inventoryService): int
    {
        $days = (int) $this->option('days');

        $this->info("Searching for stale inventory records (no updates in {$days} days)...");

        $companies = Company::query()->where('is_active', true)->get();

        $totalStaleRecords = 0;

        foreach ($companies as $company) {
            $staleRecords = $inventoryService->getStaleInventoryRecords($company->id, $days);

            if ($staleRecords->count() > 0) {
                $this->info("Company: {$company->name} - Found {$staleRecords->count()} stale products");

                foreach ($staleRecords as $product) {
                    $this->line("  - Product: {$product->sku} - {$product->name}");
                    $product->update(['is_active' => false]);
                }

                $totalStaleRecords += $staleRecords->count();
            }
        }

        if ($totalStaleRecords === 0) {
            $this->info('No stale inventory records found.');
        } else {
            $this->info("Total stale inventory records found: {$totalStaleRecords}");
        }

        return self::SUCCESS;
    }
}
