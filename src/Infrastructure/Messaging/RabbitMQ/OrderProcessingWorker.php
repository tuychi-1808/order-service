<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging\RabbitMQ;

use App\Domain\Order\ValueObject\OrderStatus;
use App\Infrastructure\Persistence\Postgres\OrderRepository;
use App\Infrastructure\Persistence\Postgres\PdoFactory;
use PhpAmqpLib\Connection\AMQPStreamConnection;

final class OrderProcessingWorker
{
    public function run(): void
    {
        $connection = new AMQPStreamConnection(
            'rabbitmq',
            5672,
            'guest',
            'guest'
        );

        $channel = $connection->channel();

        $channel->queue_declare(
            'order.created',
            false,
            false,
            false,
            false
        );

        $repository = new OrderRepository(PdoFactory::create());

        while (true) {
            $message = $channel->basic_get('order.created', false);

            if ($message === null) {
                sleep(1);
                continue;
            }

            $payload = json_decode($message->body, true);

            if (!is_array($payload) || !isset($payload['orderId'])) {
                $channel->basic_ack($message->delivery_info['delivery_tag']);
                continue;
            }

            $orderId = (int) $payload['orderId'];

            $order = $repository->findById($orderId);

            if ($order === null) {
                $channel->basic_ack($message->delivery_info['delivery_tag']);
                continue;
            }

            $repository->updateStatus($orderId, OrderStatus::PROCESSING);

            sleep(2);

            $repository->updateStatus($orderId, OrderStatus::PROCESSED);

            $channel->basic_ack($message->delivery_info['delivery_tag']);
        }
    }
}