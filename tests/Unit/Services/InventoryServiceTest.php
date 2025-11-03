<?php

declare(strict_types=1);

use App\Domain\Inventory\DTO\InventoryEntryData;
use App\Domain\Inventory\Repository\InventoryRepositoryInterface;
use App\Domain\Inventory\Repository\ProductRepositoryInterface;
use App\Domain\Inventory\Service\InventoryService;
use App\Models\Company;
use App\Models\InventoryEntry;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\mock;

beforeEach(function () {
    $this->inventoryRepository = mock(InventoryRepositoryInterface::class);
    $this->productRepository = mock(ProductRepositoryInterface::class);
    $this->service = new InventoryService($this->inventoryRepository, $this->productRepository);
});

it('registers an inventory entry successfully', function () {
    $company = Company::factory()->make(['id' => 1]);
    $product = Product::factory()->make([
        'id' => 1,
        'company_id' => 1,
        'cost_price' => 100.00,
    ]);

    $data = new InventoryEntryData(
        companyId: 1,
        productId: 1,
        quantity: 50,
        unitCost: 100.00,
        notes: 'Test entry'
    );

    $this->productRepository
        ->shouldReceive('findById')
        ->with(1)
        ->once()
        ->andReturn($product);

    $entry = InventoryEntry::factory()->make([
        'company_id' => 1,
        'product_id' => 1,
        'quantity' => 50,
        'unit_cost' => 100.00,
    ]);

    $this->inventoryRepository
        ->shouldReceive('createEntry')
        ->once()
        ->andReturn($entry);

    Cache::shouldReceive('forget')
        ->with('inventory_status_1')
        ->once();

    $result = $this->service->registerEntry($data);

    expect($result)->toBeInstanceOf(InventoryEntry::class);
});

it('throws exception when product not found', function () {
    $data = new InventoryEntryData(
        companyId: 1,
        productId: 999,
        quantity: 50,
        unitCost: 100.00
    );

    $this->productRepository
        ->shouldReceive('findById')
        ->with(999)
        ->once()
        ->andReturn(null);

    $this->service->registerEntry($data);
})->throws(InvalidArgumentException::class, 'Product not found');

it('throws exception when product does not belong to company', function () {
    $product = Product::factory()->make([
        'id' => 1,
        'company_id' => 2,
    ]);

    $data = new InventoryEntryData(
        companyId: 1,
        productId: 1,
        quantity: 50,
        unitCost: 100.00
    );

    $this->productRepository
        ->shouldReceive('findById')
        ->with(1)
        ->once()
        ->andReturn($product);

    $this->service->registerEntry($data);
})->throws(InvalidArgumentException::class, 'Product does not belong to this company');

it('checks available stock correctly', function () {
    $this->inventoryRepository
        ->shouldReceive('hasAvailableStock')
        ->with(1, 50)
        ->once()
        ->andReturn(true);

    $result = $this->service->hasAvailableStock(1, 50);

    expect($result)->toBeTrue();
});

it('creates inventory exit successfully', function () {
    $product = Product::factory()->make([
        'id' => 1,
        'company_id' => 1,
        'cost_price' => 100.00,
    ]);

    $this->productRepository
        ->shouldReceive('findById')
        ->with(1)
        ->once()
        ->andReturn($product);

    $this->inventoryRepository
        ->shouldReceive('hasAvailableStock')
        ->with(1, 10)
        ->once()
        ->andReturn(true);

    $entry = InventoryEntry::factory()->make([
        'type' => 'exit',
        'quantity' => 10,
    ]);

    $this->inventoryRepository
        ->shouldReceive('createEntry')
        ->once()
        ->andReturn($entry);

    Cache::shouldReceive('forget')
        ->with('inventory_status_1')
        ->once();

    $result = $this->service->createInventoryExit(1, 1, 10);

    expect($result)->toBeInstanceOf(InventoryEntry::class);
});

it('throws exception when insufficient stock for exit', function () {
    $product = Product::factory()->make([
        'id' => 1,
        'company_id' => 1,
    ]);

    $this->productRepository
        ->shouldReceive('findById')
        ->with(1)
        ->once()
        ->andReturn($product);

    $this->inventoryRepository
        ->shouldReceive('hasAvailableStock')
        ->with(1, 100)
        ->once()
        ->andReturn(false);

    $this->service->createInventoryExit(1, 1, 100);
})->throws(RuntimeException::class, 'Insufficient stock available');
