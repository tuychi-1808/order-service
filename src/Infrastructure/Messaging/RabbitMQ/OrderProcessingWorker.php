<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging\RabbitMQ;

use App\Domain\Order\ValueObject\OrderStatus;
use App\Infrastructure\Persistence\Postgres\OrderRepository;
use App\Infrastructure\Persistence\Postgres\PdoFactory;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

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

        $callback = function (AMQPMessage $message) use ($repository): void {
            $payload = json_decode($message->body, true);

            if (!is_array($payload) || !isset($payload['orderId'])) {
                $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
                return;
            }

            $orderId = (int) $payload['orderId'];

            $order = $repository->findById($orderId);

            if ($order === null) {
                $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
                return;
            }

            $repository->updateStatus($orderId, OrderStatus::PROCESSING);

            sleep(2);

            $repository->updateStatus($orderId, OrderStatus::PROCESSED);

            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        };

        $channel->basic_consume(
            'order.created',
            '',
            false,
            false,
            false,
            false,
            $callback
        );

        while (true) {
            $channel->wait();
        }
    }
}