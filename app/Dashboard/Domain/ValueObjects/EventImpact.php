<?php

declare(strict_types=1);

namespace App\Dashboard\Domain\ValueObjects;

/**
 * Human-readable description of observability impact — integrators may set payload.impact_hint.
 */
final class EventImpact
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $this->value = trim($value) ?: '—';
    }

    /**
     * Derives a display string without domain-specific rules (configurable paths live in dashboard JSON).
     */
    public static function fromGenericEnvelope(string $eventType, array $payload): self
    {
        $hint = $payload['impact_hint'] ?? $payload['impact'] ?? null;
        if (is_string($hint) && $hint !== '') {
            return new self($hint);
        }

        if (isset($payload['delta']) && is_numeric($payload['delta'])) {
            $delta = (int) $payload['delta'];
            $sign  = $delta >= 0 ? '+' : '';

            return new self("Δ {$sign}{$delta}");
        }

        return new self($eventType);
    }

    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
}
