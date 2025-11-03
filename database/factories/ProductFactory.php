<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $costPrice = fake()->randomFloat(2, 10, 500);
        $salePrice = $costPrice * fake()->randomFloat(2, 1.2, 2.5);

        return [
            'company_id' => Company::factory(),
            'sku' => fake()->unique()->bothify('SKU-####-????'),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'cost_price' => $costPrice,
            'sale_price' => round($salePrice, 2),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function forCompany(Company $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company->id,
        ]);
    }
}
