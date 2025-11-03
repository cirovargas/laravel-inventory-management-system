<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
final class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $saleDate = fake()->dateTimeBetween('-12 months', 'now');

        return [
            'company_id' => Company::factory(),
            'sale_number' => 'SALE-'.fake()->unique()->numerify('######'),
            'total_amount' => 0,
            'total_cost' => 0,
            'total_profit' => 0,
            'status' => 'pending',
            'sale_date' => $saleDate,
            'completed_at' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $completedAt = fake()->dateTimeBetween($attributes['sale_date'], 'now');

            return [
                'status' => 'completed',
                'completed_at' => $completedAt,
            ];
        });
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }

    public function forCompany(Company $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company->id,
        ]);
    }
}
