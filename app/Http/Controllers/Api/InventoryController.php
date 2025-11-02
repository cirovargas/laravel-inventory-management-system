<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Inventory\DTO\InventoryEntryData;
use App\Domain\Inventory\Service\InventoryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryEntryRequest;
use App\Http\Resources\InventoryEntryResource;
use App\Http\Resources\InventoryStatusResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventoryService,
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        // For now, using company_id = 1 as default
        // In production, this would come from authenticated user's company
        $companyId = 1;

        $inventory = $this->inventoryService->getInventoryStatus($companyId);

        return InventoryStatusResource::collection($inventory);
    }

    public function store(StoreInventoryEntryRequest $request): JsonResponse
    {
        // For now, using company_id = 1 as default
        // In production, this would come from authenticated user's company
        $companyId = 1;

        $data = new InventoryEntryData(
            companyId: $companyId,
            productId: $request->integer('product_id'),
            quantity: $request->integer('quantity'),
            unitCost: (float) $request->input('unit_cost'),
            notes: $request->string('notes')->toString(),
        );

        $entry = $this->inventoryService->registerEntry($data);

        return response()->json([
            'message' => 'Inventory entry created successfully',
            'data' => new InventoryEntryResource($entry->load('product')),
        ], 201);
    }
}

