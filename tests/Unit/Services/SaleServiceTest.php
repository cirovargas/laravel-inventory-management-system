<?php

declare(strict_types=1);

use App\Domain\Inventory\Repository\ProductRepositoryInterface;
use App\Domain\Sales\DTO\CreateSaleData;
use App\Domain\Sales\DTO\SaleItemData;
use App\Domain\Sales\Repository\SaleRepositoryInterface;
use App\Domain\Sales\Service\SaleService;
use App\Models\Company;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->saleRepository = mock(SaleRepositoryInterface::class);
    $this->productRepository = mock(ProductRepositoryInterface::class);
    $this->service = new SaleService($this->saleRepository, $this->productRepository);
});

it('creates a sale successfully', function () {
    $company = Company::factory()->create();
    $product1 = Product::factory()->for($company)->create([
        'cost_price' => 100.00,
        'sale_price' => 150.00,
    ]);

    $product2 = Product::factory()->for($company)->create([
        'cost_price' => 200.00,
        'sale_price' => 300.00,
    ]);

    $items = [
        new SaleItemData(productId: $product1->id, quantity: 2),
        new SaleItemData(productId: $product2->id, quantity: 1),
    ];

    $data = new CreateSaleData(
        companyId: $company->id,
        items: $items,
        notes: 'Test sale'
    );

    $this->productRepository
        ->shouldReceive('findById')
        ->with($product1->id)
        ->twice()
        ->andReturn($product1);

    $this->productRepository
        ->shouldReceive('findById')
        ->with($product2->id)
        ->twice()
        ->andReturn($product2);

    $this->saleRepository
        ->shouldReceive('create')
        ->once()
        ->andReturnUsing(function ($data) use ($company) {
            return Sale::query()->create($data);
        });

    $this->saleRepository
        ->shouldReceive('update')
        ->once()
        ->andReturnUsing(function ($sale, $data) {
            $sale->update($data);

            return $sale->fresh();
        });

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
})->throws(InvalidArgumentException::class, 'does not belong to this company');

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
