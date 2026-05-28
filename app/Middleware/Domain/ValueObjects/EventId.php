<?php

declare(strict_types=1);

namespace App\Middleware\Domain\ValueObjects;

use InvalidArgumentException;

final class EventId
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if (empty($trimmed)) {
            throw new InvalidArgumentException('EventId cannot be empty.');
        }

        $this->value = $trimmed;
    }

    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }
}
