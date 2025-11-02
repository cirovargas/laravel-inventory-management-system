<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Sales\Service\SaleService;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetSalesReportRequest;
use App\Http\Resources\SaleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReportController extends Controller
{
    public function __construct(
        private readonly SaleService $saleService,
    ) {
    }

    public function sales(Request $request): JsonResponse
    {
        // For now, using company_id = 1 as default
        // In production, this would come from authenticated user's company
        $companyId = 1;

        $startDate = $request->string('start_date')->toString();
        $endDate = $request->string('end_date')->toString();
        $sku = $request->string('sku')->toString() ?: null;
        $perPage = $request->integer('per_page', 15);

        $sales = $this->saleService->getSalesReport(
            companyId: $companyId,
            startDate: $startDate,
            endDate: $endDate,
            sku: $sku,
            perPage: $perPage
        );

        $metrics = $this->saleService->getSalesMetrics(
            companyId: $companyId,
            startDate: $startDate,
            endDate: $endDate,
            sku: $sku
        );

        return response()->json([
            'data' => SaleResource::collection($sales),
            'metrics' => [
                'total_sales' => $metrics['total_sales'],
                'total_amount' => number_format($metrics['total_amount'], 2, '.', ''),
                'total_profit' => number_format($metrics['total_profit'], 2, '.', ''),
                'total_quantity' => $metrics['total_quantity'],
            ],
            'pagination' => [
                'cursor' => $sales->cursor()->encode(),
                'next_cursor' => $sales->nextCursor()->encode(),
                'previous_cursor' => $sales->previousCursor()->encode(),
                'per_page' => $sales->perPage()
            ],
        ]);
    }
}

