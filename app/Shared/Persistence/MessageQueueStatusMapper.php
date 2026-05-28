<?php

declare(strict_types=1);

namespace App\Shared\Persistence;

/**
 * Maps domain/API queue statuses to message_queue persistence values.
 */
final class MessageQueueStatusMapper
{
    /** @var array<string, string> */
    private const TO_DB = [
        'PENDING'   => 'pending',
        'PROCESADO' => 'completed',
        'FALLIDO'   => 'failed',
        'pending'   => 'pending',
        'completed' => 'completed',
        'failed'    => 'failed',
    ];

    /** @var array<string, string> */
    private const FROM_DB = [
        'pending'   => 'PENDING',
        'processing'=> 'PENDING',
        'completed' => 'PROCESADO',
        'failed'    => 'FALLIDO',
        'dead_lettered' => 'FALLIDO',
    ];

    public static function toDb(string $status): string
    {
        $key = strtoupper(trim($status));

        return self::TO_DB[$key] ?? self::TO_DB[strtolower($status)] ?? 'pending';
    }

    public static function fromDb(string $status): string
    {
        return self::FROM_DB[strtolower(trim($status))] ?? 'PENDING';
    }
}
