<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InventoryStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->resource['product_id'],
            'sku' => $this->resource['sku'],
            'name' => $this->resource['name'],
            'current_stock' => $this->resource['current_stock'],
            'cost_price' => (float) $this->resource['cost_price'],
            'sale_price' => (float) $this->resource['sale_price'],
            'total_value' => $this->resource['total_value'],
            'projected_profit' => $this->resource['projected_profit'],
        ];
    }
}
