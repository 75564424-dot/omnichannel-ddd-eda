<?php

declare(strict_types=1);

namespace App\Middleware\Domain\ValueObjects;

use App\Shared\Logging\StructuredLogContext;
use Ramsey\Uuid\Uuid;

/**
 * Correlation and causation identifiers for distributed tracing (Plan_Middleware).
 */
final class CorrelationContext
{
    public function __construct(
        public readonly ?string $correlationId,
        public readonly ?string $causationId,
    ) {}

    /**
     * @param array<string, mixed> $envelope
     */
    public static function fromEnvelope(array $envelope): self
    {
        $context = self::parseEnvelope($envelope);

        if ($context->correlationId !== null) {
            return $context;
        }

        return new self(
            correlationId: self::correlationFromLogContext(),
            causationId: $context->causationId,
        );
    }

    /**
     * @param array<string, mixed> $envelope
     * @param array<string, mixed> $headers Lower-case header keys
     */
    public static function fromHttp(array $envelope, array $headers = []): self
    {
        $context = self::parseEnvelope($envelope);

        $headerCorrelation = self::normalizeUuid(
            $headers['x-correlation-id'] ?? $headers['x-correlation_id'] ?? null,
        );
        $headerCausation = self::normalizeUuid(
            $headers['x-causation-id'] ?? $headers['x-causation_id'] ?? null,
        );

        return new self(
            correlationId: $context->correlationId ?? $headerCorrelation ?? self::correlationFromLogContext(),
            causationId: $context->causationId ?? $headerCausation,
        );
    }

    /**
     * @param array<string, mixed> $envelope
     */
    private static function parseEnvelope(array $envelope): self
    {
        $correlation = self::normalizeUuid($envelope['correlation_id'] ?? null);
        $causation   = self::normalizeUuid($envelope['causation_id'] ?? null);

        if ($correlation === null && $causation !== null) {
            $correlation = $causation;
        }

        return new self($correlation, $causation);
    }

    private static function correlationFromLogContext(): ?string
    {
        $ctx = StructuredLogContext::toArray();

        return self::normalizeUuid($ctx['correlation_id'] ?? null);
    }

    private static function normalizeUuid(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return Uuid::isValid($value) ? $value : null;
    }
}
