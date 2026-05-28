<?php

declare(strict_types=1);

namespace App\Shared\Security\Services;

use App\Shared\Infrastructure\Models\AuditLogModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use App\Shared\Security\Contracts\AuditLogWriterInterface;
use Illuminate\Support\Facades\Schema;

final class AuditLogWriter implements AuditLogWriterInterface
{
    public function __construct(
        private readonly InstanceTenantContextInterface $tenantContext,
    ) {}

    public function record(
        string $action,
        string $entityType,
        string $entityId,
        ?array $changes,
        ?string $actorType = null,
        ?string $actorId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        if (! config('security.audit_enabled', true)) {
            return;
        }

        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        AuditLogModel::query()->create([
            'tenant_id'   => $this->tenantContext->tenantId(),
            'actor_type'  => $actorType,
            'actor_id'    => $actorId,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'changes'     => $changes,
            'ip_address'  => $ipAddress,
            'user_agent'  => $userAgent !== null ? mb_substr($userAgent, 0, 500) : null,
            'occurred_at' => now(),
        ]);
    }
}
