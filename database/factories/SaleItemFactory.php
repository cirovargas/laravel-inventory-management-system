<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleItem>
 */
final class SaleItemFactory extends Factory
{
    protected $model = SaleItem::class;

    public function definition(): array
    {
        $product = Product::factory()->create();
        $quantity = fake()->numberBetween(1, 20);
        $unitPrice = $product->sale_price;
        $unitCost = $product->cost_price;
        $subtotal = $quantity * $unitPrice;
        $costTotal = $quantity * $unitCost;
        $profit = $subtotal - $costTotal;

        return [
            'sale_id' => Sale::factory(),
            'company_id' => Company::factory(),
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'unit_cost' => $unitCost,
            'subtotal' => $subtotal,
            'cost_total' => $costTotal,
            'profit' => $profit,
        ];
    }

    public function forSale(Sale $sale): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_id' => $sale->id,
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(function (array $attributes) use ($product) {
            $quantity = $attributes['quantity'] ?? fake()->numberBetween(1, 20);
            $unitPrice = $product->sale_price;
            $unitCost = $product->cost_price;
            $subtotal = $quantity * $unitPrice;
            $costTotal = $quantity * $unitCost;
            $profit = $subtotal - $costTotal;

            return [
                'product_id' => $product->id,
                'unit_price' => $unitPrice,
                'unit_cost' => $unitCost,
                'subtotal' => $subtotal,
                'cost_total' => $costTotal,
                'profit' => $profit,
            ];
        });
    }
}
