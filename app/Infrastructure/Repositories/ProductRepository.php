<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Inventory\Repository\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

final class ProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): ?Product
    {
        return Product::query()->find($id);
    }

    public function findBySku(int $companyId, string $sku): ?Product
    {
        return Product::query()
            ->where('company_id', $companyId)
            ->where('sku', $sku)
            ->first();
    }

    public function getAllByCompany(int $companyId): Collection
    {
        return Product::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();
    }

    public function getActiveByCompany(int $companyId): Collection
    {
        return Product::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh();
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }
}

