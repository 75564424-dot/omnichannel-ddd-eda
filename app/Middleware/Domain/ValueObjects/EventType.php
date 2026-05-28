<?php

declare(strict_types=1);

namespace App\Middleware\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * String event type in transit; "known" types are whatever appears in the merged eventbus subscription map.
 */
final class EventType
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if (empty($trimmed)) {
            throw new InvalidArgumentException('EventType cannot be empty.');
        }

        $this->value = $trimmed;
    }

    /** @return list<string> */
    public static function knownTypes(): array
    {
        return array_keys(config('eventbus.subscriptions', []));
    }

    public function isKnown(): bool
    {
        return array_key_exists($this->value, config('eventbus.subscriptions', []));
    }

    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
}
