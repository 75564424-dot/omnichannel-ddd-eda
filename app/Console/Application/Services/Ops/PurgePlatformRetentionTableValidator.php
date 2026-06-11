<?php

declare(strict_types=1);

namespace App\Console\Application\Services\Ops;

final class PurgePlatformRetentionTableValidator
{
    /** @var list<string> */
    public const ALLOWED_TABLES = [
        'message_queue',
        'event_logs',
        'observability_metrics',
        'trace_logs',
        'event_store',
        'audit_logs',
    ];

    public function normalize(?string $table): ?string
    {
        return is_string($table) && $table !== '' ? $table : null;
    }

    public function isValid(?string $table): bool
    {
        return $table === null || in_array($table, self::ALLOWED_TABLES, true);
    }
}
