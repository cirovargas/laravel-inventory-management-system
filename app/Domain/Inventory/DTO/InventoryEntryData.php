<?php

declare(strict_types=1);

namespace App\Domain\Inventory\DTO;

final readonly class InventoryEntryData
{
    public function __construct(
        public int $companyId,
        public int $productId,
        public int $quantity,
        public float $unitCost,
        public ?string $notes = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'company_id' => $this->companyId,
            'product_id' => $this->productId,
            'type' => 'entry',
            'quantity' => $this->quantity,
            'unit_cost' => $this->unitCost,
            'notes' => $this->notes,
            'entry_date' => now(),
        ];
    }
}

