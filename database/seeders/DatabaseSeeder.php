<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');

        // Seed companies first
        $this->call(CompanySeeder::class);

        // Seed products for each company
        $this->call(ProductSeeder::class);

        // Seed initial inventory entries
        $this->call(InventorySeeder::class);

        // Seed 100k sales with items
        $this->call(SalesSeeder::class);

        // Create test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->command->info('Database seeding completed successfully!');
    }
}
