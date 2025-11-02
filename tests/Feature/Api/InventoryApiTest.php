<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\InventoryEntry;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->product = Product::factory()->forCompany($this->company)->create();
});

it('retrieves inventory status successfully', function () {
    InventoryEntry::factory()
        ->forCompany($this->company)
        ->forProduct($this->product)
        ->count(3)
        ->create();

    $response = $this->getJson('/api/inventory');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'product_id',
                    'sku',
                    'name',
                    'current_stock',
                    'cost_price',
                    'sale_price',
                    'total_value',
                    'projected_profit',
                ],
            ],
        ]);
});

it('creates inventory entry successfully', function () {
    $response = $this->postJson('/api/inventory', [
        'product_id' => $this->product->id,
        'quantity' => 100,
        'unit_cost' => 50.00,
        'notes' => 'Initial stock',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'product',
                'type',
                'quantity',
                'unit_cost',
                'notes',
                'entry_date',
                'created_at',
            ],
        ]);

    $this->assertDatabaseHas('inventory_entries', [
        'product_id' => $this->product->id,
        'quantity' => 100,
        'type' => 'entry',
    ]);
});

it('validates required fields when creating inventory entry', function () {
    $response = $this->postJson('/api/inventory', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['product_id', 'quantity', 'unit_cost']);
});

it('validates product exists when creating inventory entry', function () {
    $response = $this->postJson('/api/inventory', [
        'product_id' => 999999,
        'quantity' => 100,
        'unit_cost' => 50.00,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['product_id']);
});

it('validates quantity is positive when creating inventory entry', function () {
    $response = $this->postJson('/api/inventory', [
        'product_id' => $this->product->id,
        'quantity' => 0,
        'unit_cost' => 50.00,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['quantity']);
});

it('validates unit cost is non-negative when creating inventory entry', function () {
    $response = $this->postJson('/api/inventory', [
        'product_id' => $this->product->id,
        'quantity' => 100,
        'unit_cost' => -10.00,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['unit_cost']);
});

it('invalidates cache after creating inventory entry', function () {
    Cache::shouldReceive('forget')
        ->with('inventory_status_1')
        ->once();

    $this->postJson('/api/inventory', [
        'product_id' => $this->product->id,
        'quantity' => 100,
        'unit_cost' => 50.00,
    ]);
});

