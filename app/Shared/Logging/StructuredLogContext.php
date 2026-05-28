<?php

declare(strict_types=1);

namespace App\Shared\Logging;

/**
 * Request-scoped structured log context (Plan_Logs Fase 1).
 */
final class StructuredLogContext
{
    private static ?string $correlationId = null;
    private static ?string $eventUuid = null;
    private static ?string $actorId = null;
    private static ?string $eventType = null;
    private static ?string $origin = null;

    public static function setCorrelationId(?string $correlationId): void
    {
        self::$correlationId = $correlationId;
    }

    public static function setEventUuid(?string $eventUuid): void
    {
        self::$eventUuid = $eventUuid;
    }

    public static function setActorId(?string $actorId): void
    {
        self::$actorId = $actorId;
    }

    public static function setEventType(?string $eventType): void
    {
        self::$eventType = $eventType;
    }

    public static function setOrigin(?string $origin): void
    {
        self::$origin = $origin;
    }

    /** @return array<string, string> */
    public static function toArray(): array
    {
        return array_filter([
            'correlation_id' => self::$correlationId,
            'event_uuid'     => self::$eventUuid,
            'actor_id'       => self::$actorId,
            'event_type'     => self::$eventType,
            'origin'         => self::$origin,
        ], fn ($v) => is_string($v) && $v !== '');
    }

    public static function reset(): void
    {
        self::$correlationId = null;
        self::$eventUuid = null;
        self::$actorId = null;
        self::$eventType = null;
        self::$origin = null;
    }
}
