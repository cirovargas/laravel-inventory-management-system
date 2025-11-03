<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->product = Product::factory()->forCompany($this->company)->create();

    // Create sales with items
    $sale1 = Sale::factory()->forCompany($this->company)->completed()->create([
        'sale_date' => now()->subDays(5),
        'total_amount' => 300.00,
        'total_cost' => 200.00,
        'total_profit' => 100.00,
    ]);

    SaleItem::factory()->forSale($sale1)->forProduct($this->product)->create([
        'quantity' => 2,
        'subtotal' => 300.00,
        'cost_total' => 200.00,
        'profit' => 100.00,
    ]);

    $sale2 = Sale::factory()->forCompany($this->company)->completed()->create([
        'sale_date' => now()->subDays(3),
        'total_amount' => 450.00,
        'total_cost' => 300.00,
        'total_profit' => 150.00,
    ]);

    SaleItem::factory()->forSale($sale2)->forProduct($this->product)->create([
        'quantity' => 3,
        'subtotal' => 450.00,
        'cost_total' => 300.00,
        'profit' => 150.00,
    ]);
});

it('retrieves sales report successfully', function () {
    $response = $this->getJson('/api/reports/sales?start_date='.now()->subDays(10)->format('Y-m-d').'&end_date='.now()->format('Y-m-d').'&company_id='.$this->company->id);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'sale_number',
                    'total_amount',
                    'total_cost',
                    'total_profit',
                    'status',
                    'sale_date',
                ],
            ],
            'metrics' => [
                'total_sales',
                'total_amount',
                'total_profit',
                'total_quantity',
            ],
            'pagination' => [
                'cursor',
                'next_cursor',
                'previous_cursor',
                'per_page',
            ],
        ]);
});

it('validates required fields for sales report', function () {
    $response = $this->getJson('/api/reports/sales');

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['start_date', 'end_date']);
});

it('validates date format for sales report', function () {
    $response = $this->getJson('/api/reports/sales?start_date=invalid&end_date=invalid&company_id=1');

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['start_date', 'end_date']);
});

it('validates end date is after or equal to start date', function () {
    $response = $this->getJson('/api/reports/sales?start_date=2024-12-31&end_date=2024-01-01&company_id=1');

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['end_date']);
});

it('filters sales by date range correctly', function () {
    $response = $this->getJson('/api/reports/sales?start_date='.now()->subDays(4)->format('Y-m-d').'&end_date='.now()->format('Y-m-d').'&company_id='.$this->company->id);

    $response->assertSuccessful();

    $data = $response->json('data');

    expect($data)->toHaveCount(1);
});

it('filters sales by SKU correctly', function () {
    $anotherProduct = Product::factory()->forCompany($this->company)->create();

    $sale = Sale::factory()->forCompany($this->company)->completed()->create([
        'sale_date' => now()->subDays(2),
    ]);

    SaleItem::factory()->forSale($sale)->forProduct($anotherProduct)->create();

    $response = $this->getJson('/api/reports/sales?start_date='.now()->subDays(10)->format('Y-m-d').'&end_date='.now()->format('Y-m-d').'&sku='.$this->product->sku.'&company_id='.$this->company->id);

    $response->assertSuccessful();

    $data = $response->json('data');

    expect($data)->toHaveCount(2);
});

it('calculates metrics correctly', function () {
    $response = $this->getJson('/api/reports/sales?start_date='.now()->subDays(10)->format('Y-m-d').'&end_date='.now()->format('Y-m-d').'&company_id='.$this->company->id);

    $response->assertSuccessful();

    $metrics = $response->json('metrics');

    expect($metrics['total_sales'])->toBe(2)
        ->and((float) $metrics['total_amount'])->toBe(750.00)
        ->and((float) $metrics['total_profit'])->toBe(250.00)
        ->and($metrics['total_quantity'])->toBe(5);
});

it('paginates results correctly', function () {
    $response = $this->getJson('/api/reports/sales?start_date='.now()->subDays(10)->format('Y-m-d').'&end_date='.now()->format('Y-m-d').'&per_page=1&company_id='.$this->company->id);

    $response->assertSuccessful();

    $pagination = $response->json('pagination');

    // Cursor pagination doesn't have total or last_page
    expect($pagination['per_page'])->toBe(1);
});
