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
            'cost_price' => number_format((float) $this->resource['cost_price'], 2, '.', ''),
            'sale_price' => number_format((float) $this->resource['sale_price'], 2, '.', ''),
            'total_value' => number_format((float) $this->resource['total_value'], 2, '.', ''),
            'projected_profit' => number_format((float) $this->resource['projected_profit'], 2, '.', ''),
        ];
    }
}

