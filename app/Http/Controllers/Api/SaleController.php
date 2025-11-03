<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Sales\DTO\CreateSaleData;
use App\Domain\Sales\Service\SaleService;
use App\Events\SaleCompleted;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Resources\SaleResource;
use Illuminate\Http\JsonResponse;

final class SaleController extends Controller
{
    public function __construct(
        private readonly SaleService $saleService,
    ) {}

    public function store(StoreSaleRequest $request): JsonResponse
    {
        $companyId = $request->input('company_id');

        $data = CreateSaleData::fromArray($companyId, $request->validated());

        $sale = $this->saleService->createSale($data);

        SaleCompleted::dispatch($sale);

        return response()->json([
            'message' => 'Sale created successfully and is being processed',
            'data' => new SaleResource($sale->load('items.product')),
        ], 202);
    }

    public function show(int $id): JsonResponse
    {
        $sale = $this->saleService->getSaleById($id);

        if ($sale === null) {
            return response()->json([
                'message' => 'Sale not found',
            ], 404);
        }

        return response()->json([
            'data' => new SaleResource($sale),
        ]);
    }
}
