<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

final class OrderEventPublisher
{
    public function publishOrderCreated(int $orderId): void
    {
        $connection = new AMQPStreamConnection(
            host: 'rabbitmq',
            port: 5672,
            user: 'guest',
            password: 'guest'
        );

        $channel = $connection->channel();

        $channel->queue_declare(
            queue: 'order.created',
            passive: false,
            durable: false,
            exclusive: false,
            auto_delete: false
        );

        $payload = json_encode([
            'orderId' => $orderId,
            'eventName' => 'order.created',
            'createdAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ], JSON_THROW_ON_ERROR);

        $message = new AMQPMessage($payload);

        $channel->basic_publish($message, '', 'order.created');

        $channel->close();
        $connection->close();
    }
}
