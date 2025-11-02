<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Seeder;

final class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding products...');

        $companies = Company::all();

        foreach ($companies as $company) {
            $this->command->info("Creating 200 products for company: {$company->name}");

            Product::factory()
                ->count(200)
                ->forCompany($company)
                ->create();
        }

        $this->command->info('Products seeded successfully!');
    }
}

