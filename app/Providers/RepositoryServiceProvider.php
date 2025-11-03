<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Inventory\Repository\InventoryRepositoryInterface;
use App\Domain\Inventory\Repository\ProductRepositoryInterface;
use App\Domain\Sales\Repository\SaleRepositoryInterface;
use App\Repository\InventoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SaleRepository;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
        $this->app->bind(SaleRepositoryInterface::class, SaleRepository::class);
    }
}
