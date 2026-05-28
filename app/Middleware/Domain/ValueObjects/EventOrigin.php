<?php

declare(strict_types=1);

namespace App\Middleware\Domain\ValueObjects;

/**
 * Origin of an event as seen by the bus (channel / gateway hint only — no domain inference).
 */
final class EventOrigin
{
    public const WEB_GATEWAY = 'Web Gateway';
    public const RETAIL_POS  = 'Retail POS';
    public const UNKNOWN     = 'Unknown';

    private readonly string $value;

    public function __construct(string $value)
    {
        $this->value = trim($value) ?: self::UNKNOWN;
    }

    public static function inferFromPayload(array $payload): self
    {
        $channel = strtoupper(trim((string) ($payload['channel'] ?? '')));

        return match ($channel) {
            'WEB' => new self(self::WEB_GATEWAY),
            'POS' => new self(self::RETAIL_POS),
            'MOBILE' => new self('Mobile'),
            'PARTNER_API' => new self('Partner API'),
            default => $channel !== '' ? new self($channel) : new self(self::UNKNOWN),
        };
    }

    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
}
