<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Repository;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;

    public function findBySku(int $companyId, string $sku): ?Product;

    public function getAllByCompany(int $companyId): Collection;

    public function getActiveByCompany(int $companyId): Collection;

    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function delete(Product $product): bool;
}
