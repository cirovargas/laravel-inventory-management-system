<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class SalesSeeder extends Seeder
{
    private const TOTAL_SALES = 100000;

    private const BATCH_SIZE = 1000;

    public function run(): void
    {
        $this->command->info('Seeding sales (this may take a few minutes)...');

        $companies = Company::all();
        $salesPerCompany = (int) ceil(self::TOTAL_SALES / $companies->count());

        $progressBar = $this->command->getOutput()->createProgressBar(self::TOTAL_SALES);
        $progressBar->start();

        foreach ($companies as $company) {
            $this->seedSalesForCompany($company, $salesPerCompany, $progressBar);
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info('Sales seeded successfully!');
    }

    private function seedSalesForCompany(Company $company, int $totalSales, $progressBar): void
    {
        $products = Product::query()
            ->where('company_id', $company->id)
            ->get()
            ->toArray();

        if (count($products) === 0) {
            return;
        }

        $salesBatch = [];
        $saleItemsBatch = [];
        $saleIdCounter = DB::table('sales')->max('id') ?? 0;

        for ($i = 0; $i < $totalSales; $i++) {
            $saleIdCounter++;
            $saleDate = now()->subDays(rand(0, 365));

            // Create sale record
            $sale = [
                'id' => $saleIdCounter,
                'company_id' => $company->id,
                'sale_number' => 'SALE-'.$saleDate->format('Ymd').'-'.str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                'total_amount' => 0,
                'total_cost' => 0,
                'total_profit' => 0,
                'status' => 'completed',
                'sale_date' => $saleDate,
                'completed_at' => $saleDate->copy()->addMinutes(rand(5, 60)),
                'notes' => null,
                'created_at' => $saleDate,
                'updated_at' => $saleDate,
                'deleted_at' => null,
            ];

            // Create 2-5 sale items per sale
            $numItems = rand(2, 5);
            $totalAmount = 0;
            $totalCost = 0;

            for ($j = 0; $j < $numItems; $j++) {
                $product = $products[array_rand($products)];
                $quantity = rand(1, 10);
                $unitPrice = $product['sale_price'];
                $unitCost = $product['cost_price'];
                $subtotal = $quantity * $unitPrice;
                $costTotal = $quantity * $unitCost;
                $profit = $subtotal - $costTotal;

                $saleItemsBatch[] = [
                    'sale_id' => $saleIdCounter,
                    'company_id' => $company->id,
                    'product_id' => $product['id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                    'cost_total' => $costTotal,
                    'profit' => $profit,
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate,
                ];

                $totalAmount += $subtotal;
                $totalCost += $costTotal;
            }

            $sale['total_amount'] = $totalAmount;
            $sale['total_cost'] = $totalCost;
            $sale['total_profit'] = $totalAmount - $totalCost;

            $salesBatch[] = $sale;

            // Insert in batches
            if (count($salesBatch) >= self::BATCH_SIZE) {
                DB::table('sales')->insert($salesBatch);
                DB::table('sale_items')->insert($saleItemsBatch);

                $salesBatch = [];
                $saleItemsBatch = [];
            }

            $progressBar->advance();
        }

        // Insert remaining records
        if (count($salesBatch) > 0) {
            DB::table('sales')->insert($salesBatch);
            DB::table('sale_items')->insert($saleItemsBatch);
        }
    }
}

