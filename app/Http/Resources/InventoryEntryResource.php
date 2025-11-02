<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InventoryEntryResource extends JsonResource
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
            'type' => $this->type,
            'quantity' => $this->quantity,
            'unit_cost' => number_format((float) $this->unit_cost, 2, '.', ''),
            'notes' => $this->notes,
            'entry_date' => $this->entry_date->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}

