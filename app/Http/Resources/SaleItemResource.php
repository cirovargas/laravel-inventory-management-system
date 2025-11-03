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
            'unit_price' => (float) $this->unit_price,
            'unit_cost' => (float) $this->unit_cost,
            'subtotal' => (float) $this->subtotal,
            'cost_total' => (float) $this->cost_total,
            'profit' => (float) $this->profit,
        ];
    }
}
