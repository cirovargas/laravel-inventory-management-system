<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Sales\DTO\CreateSaleData;
use App\Domain\Sales\Service\SaleService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Resources\SaleResource;
use App\Jobs\ProcessSaleJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

final class SaleController extends Controller
{
    public function __construct(
        private readonly SaleService $saleService,
    ) {}

    public function store(StoreSaleRequest $request): JsonResponse
    {
        $companyId = $request->input('company_id');

        $data = CreateSaleData::fromArray($companyId, $request->validated());

        $trackingId = Str::uuid7()->toString();
        ProcessSaleJob::dispatch($data, $trackingId);

        return response()->json([
            'message' => 'Sale is being processed',
            'tracking_id' => $trackingId,
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
