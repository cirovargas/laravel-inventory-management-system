<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

final class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding companies...');

        Company::factory()->count(3)->create();

        $this->command->info('Companies seeded successfully!');
    }
}

