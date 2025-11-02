<?php

declare(strict_types=1);

namespace App\Domain\Sales\DTO;

final readonly class CreateSaleData
{
    /**
     * @param  array<SaleItemData>  $items
     */
    public function __construct(
        public int $companyId,
        public array $items,
        public ?string $notes = null,
    ) {
    }

    public static function fromArray(int $companyId, array $data): self
    {
        $items = array_map(
            fn (array $item) => SaleItemData::fromArray($item),
            $data['items']
        );

        return new self(
            companyId: $companyId,
            items: $items,
            notes: $data['notes'] ?? null,
        );
    }
}

