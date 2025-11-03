<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sale_number' => $this->sale_number,
            'total_amount' => number_format((float) $this->total_amount, 2, '.', ''),
            'total_cost' => number_format((float) $this->total_cost, 2, '.', ''),
            'total_profit' => number_format((float) $this->total_profit, 2, '.', ''),
            'status' => $this->status,
            'sale_date' => $this->sale_date->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'notes' => $this->notes,
            'items' => SaleItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
