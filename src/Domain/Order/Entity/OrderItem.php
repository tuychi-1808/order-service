<?php

declare(strict_types=1);

namespace App\Domain\Order\Entity;

final class OrderItem
{
    public function __construct(
        private int $productId,
        private int $quantity
    ) {
        if ($this->productId <= 0) {
            throw new \InvalidArgumentException('Product id must be greater than 0');
        }

        if ($this->quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than 0');
        }
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }
}