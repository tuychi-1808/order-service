<?php

declare(strict_types=1);

namespace App\Domain\Order\Entity;

use App\Domain\Order\ValueObject\OrderStatus;

final class Order
{
    /**
     * @param OrderItem[] $items
     */
    public function __construct(
        private ?int $id,
        private int $customerId,
        private OrderStatus $status,
        private array $items,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt
    ) {
        if ($this->customerId <= 0) {
            throw new \InvalidArgumentException('Customer id must be greater than 0');
        }

        if ($this->items === []) {
            throw new \InvalidArgumentException('Order must contain at least one item');
        }

        foreach ($this->items as $item) {
            if (!$item instanceof OrderItem) {
                throw new \InvalidArgumentException('Each item must be an instance of OrderItem');
            }
        }
    }

    /**
     * @param OrderItem[] $items
     */
    public static function create(int $customerId, array $items): self
    {
        $now = new \DateTimeImmutable();

        return new self(
            id: null,
            customerId: $customerId,
            status: new OrderStatus(OrderStatus::NEW),
            items: $items,
            createdAt: $now,
            updatedAt: $now
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    /**
     * @return OrderItem[]
     */
    public function items(): array
    {
        return $this->items;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function markProcessing(): void
    {
        $this->status = new OrderStatus(OrderStatus::PROCESSING);
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markProcessed(): void
    {
        $this->status = new OrderStatus(OrderStatus::PROCESSED);
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markFailed(): void
    {
        $this->status = new OrderStatus(OrderStatus::FAILED);
        $this->updatedAt = new \DateTimeImmutable();
    }
}