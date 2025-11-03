<?php

declare(strict_types=1);

use App\Domain\Inventory\Repository\ProductRepositoryInterface;
use App\Domain\Sales\DTO\CreateSaleData;
use App\Domain\Sales\DTO\SaleItemData;
use App\Domain\Sales\Repository\SaleRepositoryInterface;
use App\Domain\Sales\Service\SaleService;
use App\Models\Product;
use App\Models\Sale;

use function Pest\Laravel\mock;

beforeEach(function () {
    $this->saleRepository = mock(SaleRepositoryInterface::class);
    $this->productRepository = mock(ProductRepositoryInterface::class);
    $this->service = new SaleService($this->saleRepository, $this->productRepository);
});

it('creates a sale successfully', function () {
    $product1 = Product::factory()->make([
        'id' => 1,
        'company_id' => 1,
        'cost_price' => 100.00,
        'sale_price' => 150.00,
    ]);

    $product2 = Product::factory()->make([
        'id' => 2,
        'company_id' => 1,
        'cost_price' => 200.00,
        'sale_price' => 300.00,
    ]);

    $items = [
        new SaleItemData(productId: 1, quantity: 2),
        new SaleItemData(productId: 2, quantity: 1),
    ];

    $data = new CreateSaleData(
        companyId: 1,
        items: $items,
        notes: 'Test sale'
    );

    $this->productRepository
        ->shouldReceive('findById')
        ->with(1)
        ->once()
        ->andReturn($product1);

    $this->productRepository
        ->shouldReceive('findById')
        ->with(2)
        ->once()
        ->andReturn($product2);

    $sale = Sale::factory()->make(['id' => 1]);

    $this->saleRepository
        ->shouldReceive('create')
        ->once()
        ->andReturn($sale);

    $this->productRepository
        ->shouldReceive('findById')
        ->with(1)
        ->once()
        ->andReturn($product1);

    $this->productRepository
        ->shouldReceive('findById')
        ->with(2)
        ->once()
        ->andReturn($product2);

    $updatedSale = Sale::factory()->make([
        'id' => 1,
        'total_amount' => 600.00,
        'total_cost' => 400.00,
        'total_profit' => 200.00,
    ]);

    $this->saleRepository
        ->shouldReceive('update')
        ->once()
        ->andReturn($updatedSale);

    $result = $this->service->createSale($data);

    expect($result)->toBeInstanceOf(Sale::class);
});

it('throws exception when product not found during sale creation', function () {
    $items = [
        new SaleItemData(productId: 999, quantity: 2),
    ];

    $data = new CreateSaleData(
        companyId: 1,
        items: $items
    );

    $this->productRepository
        ->shouldReceive('findById')
        ->with(999)
        ->once()
        ->andReturn(null);

    $this->service->createSale($data);
})->throws(InvalidArgumentException::class, 'Product 999 not found');

it('throws exception when product does not belong to company', function () {
    $product = Product::factory()->make([
        'id' => 1,
        'company_id' => 2,
    ]);

    $items = [
        new SaleItemData(productId: 1, quantity: 2),
    ];

    $data = new CreateSaleData(
        companyId: 1,
        items: $items
    );

    $this->productRepository
        ->shouldReceive('findById')
        ->with(1)
        ->once()
        ->andReturn($product);

    $this->service->createSale($data);
})->throws(InvalidArgumentException::class, 'Product 1 does not belong to this company');

it('retrieves sale by id', function () {
    $sale = Sale::factory()->make(['id' => 1]);

    $this->saleRepository
        ->shouldReceive('findByIdWithItems')
        ->with(1)
        ->once()
        ->andReturn($sale);

    $result = $this->service->getSaleById(1);

    expect($result)->toBeInstanceOf(Sale::class);
});

it('returns null when sale not found', function () {
    $this->saleRepository
        ->shouldReceive('findByIdWithItems')
        ->with(999)
        ->once()
        ->andReturn(null);

    $result = $this->service->getSaleById(999);

    expect($result)->toBeNull();
});
