<?php

declare(strict_types=1);

namespace App\Domain\Order\ValueObject;

final class OrderStatus
{
    public const NEW = 'new';
    public const PROCESSING = 'processing';
    public const PROCESSED = 'processed';
    public const FAILED = 'failed';

    public function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function is(string $status): bool
    {
        return $this->value === $status;
    }

    private function validate(string $value): void
    {
        $allowedStatuses = [
            self::NEW,
            self::PROCESSING,
            self::PROCESSED,
            self::FAILED,
        ];

        if (!in_array($value, $allowedStatuses, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid order status: %s', $value));
        }
    }
}