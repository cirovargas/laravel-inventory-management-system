<?php

declare(strict_types=1);

use App\Events\SaleCompleted;
use App\Models\Company;
use App\Models\InventoryEntry;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->product1 = Product::factory()->forCompany($this->company)->create([
        'cost_price' => 100.00,
        'sale_price' => 150.00,
    ]);
    $this->product2 = Product::factory()->forCompany($this->company)->create([
        'cost_price' => 200.00,
        'sale_price' => 300.00,
    ]);

    // Add initial stock
    InventoryEntry::factory()
        ->forCompany($this->company)
        ->forProduct($this->product1)
        ->create(['quantity' => 100]);

    InventoryEntry::factory()
        ->forCompany($this->company)
        ->forProduct($this->product2)
        ->create(['quantity' => 50]);
});

it('creates a sale successfully', function () {
    Event::fake();

    $response = $this->postJson('/api/sales', [
        'items' => [
            ['product_id' => $this->product1->id, 'quantity' => 2],
            ['product_id' => $this->product2->id, 'quantity' => 1],
        ],
        'notes' => 'Test sale',
    ]);

    $response->assertAccepted()
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'sale_number',
                'total_amount',
                'total_cost',
                'total_profit',
                'status',
                'sale_date',
                'notes',
                'items',
            ],
        ]);

    $this->assertDatabaseHas('sales', [
        'company_id' => $this->company->id,
        'status' => 'pending',
    ]);

    $this->assertDatabaseHas('sale_items', [
        'product_id' => $this->product1->id,
        'quantity' => 2,
    ]);

    Event::assertDispatched(SaleCompleted::class);
});

it('validates required fields when creating sale', function () {
    $response = $this->postJson('/api/sales', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['items']);
});

it('validates items array is not empty', function () {
    $response = $this->postJson('/api/sales', [
        'items' => [],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['items']);
});

it('validates each item has product_id and quantity', function () {
    $response = $this->postJson('/api/sales', [
        'items' => [
            ['quantity' => 2],
        ],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['items.0.product_id']);
});

it('validates product exists in items', function () {
    $response = $this->postJson('/api/sales', [
        'items' => [
            ['product_id' => 999999, 'quantity' => 2],
        ],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['items.0.product_id']);
});

it('validates quantity is positive in items', function () {
    $response = $this->postJson('/api/sales', [
        'items' => [
            ['product_id' => $this->product1->id, 'quantity' => 0],
        ],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['items.0.quantity']);
});

it('retrieves a sale by id', function () {
    $sale = Sale::factory()
        ->forCompany($this->company)
        ->completed()
        ->create();

    $response = $this->getJson("/api/sales/{$sale->id}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id',
                'sale_number',
                'total_amount',
                'total_cost',
                'total_profit',
                'status',
                'sale_date',
            ],
        ]);
});

it('returns 404 when sale not found', function () {
    $response = $this->getJson('/api/sales/999999');

    $response->assertNotFound();
});

it('calculates totals correctly when creating sale', function () {
    $response = $this->postJson('/api/sales', [
        'items' => [
            ['product_id' => $this->product1->id, 'quantity' => 2],
            ['product_id' => $this->product2->id, 'quantity' => 1],
        ],
    ]);

    $response->assertAccepted();

    $sale = Sale::query()->latest()->first();

    expect($sale->total_amount)->toBe(600.00)
        ->and($sale->total_cost)->toBe(400.00)
        ->and($sale->total_profit)->toBe(200.00);
});

