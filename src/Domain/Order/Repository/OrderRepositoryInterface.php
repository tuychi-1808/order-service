<?php

declare(strict_types=1);

namespace App\Domain\Order\Repository;

use App\Domain\Order\Entity\Order;

interface OrderRepositoryInterface
{
    public function save(Order $order): int;
    public function findById(int $id): ?order;

    /**
     * @return Order[]
     */
    public function findAll(): array;
    public function updateStatus(int $id, string $status): void;
}