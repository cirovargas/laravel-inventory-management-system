<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\InventoryEntry;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryEntry>
 */
final class InventoryEntryFactory extends Factory
{
    protected $model = InventoryEntry::class;

    public function definition(): array
    {
        $product = Product::factory()->create();

        return [
            'company_id' => $product->company_id,
            'product_id' => $product->id,
            'type' => 'entry',
            'quantity' => fake()->numberBetween(10, 500),
            'unit_cost' => $product->cost_price,
            'notes' => fake()->optional()->sentence(),
            'entry_date' => fake()->dateTimeBetween('-12 months', 'now'),
        ];
    }

    public function entry(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'entry',
            'quantity' => abs($attributes['quantity'] ?? fake()->numberBetween(10, 500)),
        ]);
    }

    public function exit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'exit',
            'quantity' => abs($attributes['quantity'] ?? fake()->numberBetween(1, 50)),
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $product->company_id,
            'product_id' => $product->id,
            'unit_cost' => $product->cost_price,
        ]);
    }
}

