<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\Order\CreateOrder\CreateOrderCommand;
use App\Application\Order\CreateOrder\CreateOrderHandler;
use App\Infrastructure\Messaging\RabbitMQ\OrderEventPublisher;
use App\Infrastructure\Persistence\Postgres\OrderRepository;
use App\Infrastructure\Container\AppContainer;
use App\Infrastructure\Persistence\Postgres\PdoFactory;
use Symfony\Component\HttpFoundation\Request;

final class OrderController
{
    public function list(): void
    {
        $pdo = PdoFactory::create();
        $repository = new OrderRepository($pdo);

        $orders = $repository->findAll();

        header('Content-Type: application/json');

        echo json_encode(array_map(
            static fn($order) => [
                'id' => $order->id(),
                'customerId' => $order->customerId(),
                'status' => $order->status()->value(),
                'createdAt' => $order->createdAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $order->updatedAt()->format('Y-m-d H:i:s'),
            ],
            $orders
        ));
    }

    public function create(Request $request): void
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid JSON payload']);
            return;
        }

        if (
            !isset($payload['customerId']) ||
            !is_int($payload['customerId']) ||
            !isset($payload['items']) ||
            !is_array($payload['items']) ||
            $payload['items'] === []
        ) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid request data']);
            return;
        }

        $container = new AppContainer();

        $handler = $container->createOrderHandler();
        $publisher = $container->orderEventPublisher();

        $command = new CreateOrderCommand(
            customerId: $payload['customerId'],
            items: $payload['items']
        );

        $orderId = $handler->handle($command);

        $publisher->publishOrderCreated($orderId);

        http_response_code(201);
        header('Content-Type: application/json');

        echo json_encode([
            'id' => $orderId,
            'status' => 'new'
        ]);
    }

    public function show(int $id): void
    {
        $pdo = PdoFactory::create();
        $repository = new OrderRepository($pdo);

        $order = $repository->findById($id);

        if ($order === null) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Order not found']);
            return;
        }

        header('Content-Type: application/json');

        echo json_encode([
            'id' => $order->id(),
            'customerId' => $order->customerId(),
            'status' => $order->status()->value(),
            'items' => array_map(
                static fn($item) => [
                    'productId' => $item->productId(),
                    'quantity' => $item->quantity(),
                ],
                $order->items()
            ),
            'createdAt' => $order->createdAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $order->updatedAt()->format('Y-m-d H:i:s'),
        ]);
    }
}