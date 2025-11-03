<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Sales\Service\SaleService;
use App\Http\Controllers\Controller;
use App\Http\Requests\SalesReportRequest;
use App\Http\Resources\SaleResource;
use Illuminate\Http\JsonResponse;
use Laravel\Octane\Facades\Octane;

final class ReportController extends Controller
{
    public function __construct(
        private readonly SaleService $saleService,
    ) {}

    public function sales(SalesReportRequest $request): JsonResponse
    {
        $companyId = $request->integer('company_id');
        $startDate = $request->string('start_date')->toString();
        $endDate = $request->string('end_date')->toString();
        $sku = $request->string('sku')->toString() ?: null;
        $perPage = $request->integer('per_page', 15);

        [$metrics, $sales] = Octane::concurrently([
            fn () => $this->saleService->getSalesMetrics(
                companyId: $companyId,
                startDate: $startDate,
                endDate: $endDate,
                sku: $sku
            ),
            fn () => $this->saleService->getSalesReport(
                companyId: $companyId,
                startDate: $startDate,
                endDate: $endDate,
                sku: $sku,
                perPage: $perPage
            ),
        ]);

        return response()->json([
            'data' => SaleResource::collection($sales),
            'metrics' => [
                'total_sales' => $metrics['total_sales'],
                'total_amount' => $metrics['total_amount'],
                'total_profit' => $metrics['total_profit'],
                'total_quantity' => $metrics['total_quantity'],
            ],
            'pagination' => [
                'cursor' => $sales->cursor()?->encode(),
                'next_cursor' => $sales->nextCursor()?->encode(),
                'previous_cursor' => $sales->previousCursor()?->encode(),
                'per_page' => $sales->perPage(),
            ],
        ]);
    }
}
