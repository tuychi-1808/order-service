<?php

declare(strict_types=1);

namespace App\Application\Order\CreateOrder;

final class CreateOrderCommand
{
    /**
     * @param array<array{productId:int, quantity:int}> $items
     */
    public function __construct(
        private int $customerId,
        private array $items
    ) {
    }

    public function customerId(): int
    {
        return $this->customerId;
    }

    /**
     * @return array<array{productId:int, quantity:int}>
     */
    public function items(): array
    {
        return $this->items;
    }
}