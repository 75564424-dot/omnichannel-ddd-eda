<?php

declare(strict_types=1);

namespace App\Dashboard\Domain\ValueObjects;

/**
 * Encapsulates the origin of an event as observed by the Dashboard read model.
 */
final class EventOrigin
{
    public const UNKNOWN = 'Unknown';

    private readonly string $value;

    public function __construct(string $value)
    {
        $this->value = trim($value) ?: self::UNKNOWN;
    }

    public static function fromEventPayload(string $eventType, array $payload): self
    {
        unset($eventType);
        $explicit = $payload['origin'] ?? $payload['source_system'] ?? null;
        if (is_string($explicit) && $explicit !== '') {
            return new self($explicit);
        }

        $ch = strtoupper(trim((string) ($payload['channel'] ?? '')));

        return match ($ch) {
            'WEB' => new self('Web'),
            'POS' => new self('POS'),
            'MOBILE' => new self('Mobile'),
            'PARTNER_API' => new self('Partner API'),
            '' => new self(self::UNKNOWN),
            default => new self($ch),
        };
    }

    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
}
