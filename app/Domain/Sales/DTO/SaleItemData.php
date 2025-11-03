<?php

declare(strict_types=1);

namespace App\Domain\Sales\DTO;

final readonly class SaleItemData
{
    public function __construct(
        public int $productId,
        public int $quantity,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            productId: $data['product_id'],
            quantity: $data['quantity'],
        );
    }
}
