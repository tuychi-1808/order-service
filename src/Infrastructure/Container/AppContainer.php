<?php

declare(strict_types=1);

namespace App\Infrastructure\Container;

use App\Application\Order\CreateOrder\CreateOrderHandler;
use App\Infrastructure\Messaging\RabbitMQ\OrderEventPublisher;
use App\Infrastructure\Persistence\Postgres\OrderRepository;
use App\Infrastructure\Persistence\Postgres\PdoFactory;

final class AppContainer
{
    private ?\PDO $pdo = null;
    private ?OrderRepository $orderRepository = null;
    private ?CreateOrderHandler $createOrderHandler = null;
    private ?OrderEventPublisher $orderEventPublisher = null;

    public function pdo(): \PDO
    {
        if ($this->pdo === null) {
            $this->pdo = PdoFactory::create();
        }

        return $this->pdo;
    }

    public function orderRepository(): OrderRepository
    {
        if ($this->orderRepository === null) {
            $this->orderRepository = new OrderRepository($this->pdo());
        }

        return $this->orderRepository;
    }

    public function createOrderHandler(): CreateOrderHandler
    {
        if ($this->createOrderHandler === null) {
            $this->createOrderHandler = new CreateOrderHandler(
                $this->orderRepository()
            );
        }

        return $this->createOrderHandler;
    }

    public function orderEventPublisher(): OrderEventPublisher
    {
        if ($this->orderEventPublisher === null) {
            $this->orderEventPublisher = new OrderEventPublisher();
        }

        return $this->orderEventPublisher;
    }
}