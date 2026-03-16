<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Postgres;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Entity\OrderItem;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Order\ValueObject\OrderStatus;

final class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private \PDO $pdo
    ) {
    }

    public function save(Order $order): int
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO orders (customer_id, status, created_at, updated_at)
                VALUES (:customer_id, :status, :created_at, :updated_at)
                RETURNING id
            ');

            $stmt->execute([
                'customer_id' => $order->customerId(),
                'status' => $order->status()->value(),
                'created_at' => $order->createdAt()->format('Y-m-d H:i:s'),
                'updated_at' => $order->updatedAt()->format('Y-m-d H:i:s'),
            ]);

            $orderId = (int) $stmt->fetchColumn();

            $itemStmt = $this->pdo->prepare('
                INSERT INTO order_items (order_id, product_id, quantity)
                VALUES (:order_id, :product_id, :quantity)
            ');

            foreach ($order->items() as $item) {
                $itemStmt->execute([
                    'order_id' => $orderId,
                    'product_id' => $item->productId(),
                    'quantity' => $item->quantity(),
                ]);
            }

            $this->pdo->commit();

            return $orderId;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function findById(int $id): ?Order
    {
        $stmt = $this->pdo->prepare('
            SELECT id, customer_id, status, created_at, updated_at
            FROM orders
            WHERE id = :id
        ');

        $stmt->execute(['id' => $id]);
        $orderRow = $stmt->fetch();

        if (!$orderRow) {
            return null;
        }

        return $this->hydrateOrder($orderRow);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('
            SELECT id, customer_id, status, created_at, updated_at
            FROM orders
            ORDER BY id
        ');

        $orders = [];

        foreach ($stmt->fetchAll() as $orderRow) {
            $orders[] = $this->hydrateOrder($orderRow);
        }

        return $orders;
    }

    public function updateStatus(int $id, string $status): void
    {
        new OrderStatus($status);

        $stmt = $this->pdo->prepare('
            UPDATE orders
            SET status = :status, updated_at = :updated_at
            WHERE id = :id
        ');

        $stmt->execute([
            'id' => $id,
            'status' => $status,
            'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    private function hydrateOrder(array $orderRow): Order
    {
        $itemStmt = $this->pdo->prepare('
            SELECT product_id, quantity
            FROM order_items
            WHERE order_id = :order_id
            ORDER BY id
        ');

        $itemStmt->execute(['order_id' => (int) $orderRow['id']]);
        $itemRows = $itemStmt->fetchAll();

        $items = [];
        foreach ($itemRows as $itemRow) {
            $items[] = new OrderItem(
                productId: (int) $itemRow['product_id'],
                quantity: (int) $itemRow['quantity']
            );
        }

        return new Order(
            id: (int) $orderRow['id'],
            customerId: (int) $orderRow['customer_id'],
            status: new OrderStatus($orderRow['status']),
            items: $items,
            createdAt: new \DateTimeImmutable($orderRow['created_at']),
            updatedAt: new \DateTimeImmutable($orderRow['updated_at'])
        );
    }
}