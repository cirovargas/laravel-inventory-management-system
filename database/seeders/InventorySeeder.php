<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding inventory entries...');

        $companies = Company::all();

        foreach ($companies as $company) {
            $this->command->info("Creating inventory entries for company: {$company->name}");

            $products = Product::query()
                ->where('company_id', $company->id)
                ->get();

            $entries = [];
            $batchSize = 500;

            foreach ($products as $product) {
                // Create 2-5 initial stock entries per product
                $numEntries = rand(2, 5);

                for ($i = 0; $i < $numEntries; $i++) {
                    $entries[] = [
                        'company_id' => $company->id,
                        'product_id' => $product->id,
                        'type' => 'entry',
                        'quantity' => rand(50, 500),
                        'unit_cost' => $product->cost_price,
                        'notes' => null,
                        'sale_id' => null,
                        'sale_date' => null,
                        'entry_date' => now()->subDays(rand(1, 365)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (count($entries) >= $batchSize) {
                        DB::table('inventory_entries')->insert($entries);
                        $entries = [];
                    }
                }
            }

            if (count($entries) > 0) {
                DB::table('inventory_entries')->insert($entries);
            }
        }

        $this->command->info('Inventory entries seeded successfully!');
    }
}
