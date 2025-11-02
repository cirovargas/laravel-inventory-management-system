<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class SaleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => [
                'id' => $this->product->id,
                'sku' => $this->product->sku,
                'name' => $this->product->name,
            ],
            'quantity' => $this->quantity,
            'unit_price' => number_format((float) $this->unit_price, 2, '.', ''),
            'unit_cost' => number_format((float) $this->unit_cost, 2, '.', ''),
            'subtotal' => number_format((float) $this->subtotal, 2, '.', ''),
            'cost_total' => number_format((float) $this->cost_total, 2, '.', ''),
            'profit' => number_format((float) $this->profit, 2, '.', ''),
        ];
    }
}

