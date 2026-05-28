<?php

declare(strict_types=1);

namespace App\Shared\Persistence;

/**
 * Maps bus status strings to numeric observability metric values.
 */
final class BusStatusMetricMapper
{
    /** @var array<string, int> */
    private const TO_NUMERIC = [
        'ACTIVE'   => 1,
        'DEGRADED' => 2,
        'HI-LOAD'  => 3,
        'STOPPED'  => 0,
    ];

    /** @var array<int, string> */
    private const FROM_NUMERIC = [
        1 => 'ACTIVE',
        2 => 'DEGRADED',
        3 => 'HI-LOAD',
        0 => 'STOPPED',
    ];

    public static function toNumeric(string $status): int
    {
        return self::TO_NUMERIC[strtoupper(trim($status))] ?? 0;
    }

    public static function fromNumeric(int|float $value): string
    {
        return self::FROM_NUMERIC[(int) $value] ?? 'STOPPED';
    }
}
