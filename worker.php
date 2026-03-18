<?php

declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', '0');

require __DIR__ . '/vendor/autoload.php';

while (true) {
    try {
        $worker = new \App\Infrastructure\Messaging\RabbitMQ\OrderProcessingWorker();
        $worker->run();
    } catch (\Throwable $e) {
        fwrite(STDOUT, sprintf("[worker] error: %s\n", $e->getMessage()));
        sleep(3);
    }
}