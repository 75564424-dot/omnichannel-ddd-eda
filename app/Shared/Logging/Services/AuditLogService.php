<?php

declare(strict_types=1);

namespace App\Shared\Logging\Services;

use App\Shared\Security\Contracts\AuditLogWriterInterface;

/**
 * Application service for compliance audit trail → audit_logs (Plan_Logs Fase 2).
 */
final class AuditLogService
{
    public function __construct(
        private readonly AuditLogWriterInterface $writer,
    ) {}

    public function record(
        string $action,
        string $entityType,
        string $entityId,
        ?array $changes = null,
        ?string $actorType = null,
        ?string $actorId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        $this->writer->record(
            action: $action,
            entityType: $entityType,
            entityId: $entityId,
            changes: $changes,
            actorType: $actorType,
            actorId: $actorId,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );
    }
}
