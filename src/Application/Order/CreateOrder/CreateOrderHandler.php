<?php

declare(strict_types=1);

namespace App\Application\Order\CreateOrder;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Entity\OrderItem;
use App\Domain\Order\Repository\OrderRepositoryInterface;

final class CreateOrderHandler
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {
    }

    public function handle(CreateOrderCommand $command): int
    {
        $items = [];

        foreach ($command->items() as $itemData) {
            $items[] = new OrderItem(
                productId: $itemData['productId'],
                quantity: $itemData['quantity']
            );
        }

        $order = Order::create(
            customerId: $command->customerId(),
            items: $items
        );

        return $this->orderRepository->save($order);
    }
}