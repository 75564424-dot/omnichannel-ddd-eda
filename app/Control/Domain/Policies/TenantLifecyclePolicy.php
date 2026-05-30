<?php

declare(strict_types=1);

namespace App\Control\Domain\Policies;

use App\Shared\Infrastructure\Models\TenantModel;

final class TenantLifecyclePolicy
{
    /**
     * Validates if a "Start" operation is allowed for the given tenant status and lifecycle.
     */
    public static function canStart(string $status, string $lifecycle): bool
    {
        if ($status === 'suspended') {
            return false;
        }

        return in_array($lifecycle, ['provisioned', 'stopped', 'running'], true);
    }

    /**
     * Validates if a "Suspend" operation is allowed for the given tenant status and lifecycle.
     */
    public static function canSuspend(string $status, string $lifecycle): bool
    {
        return $status === 'active';
    }

    /**
     * Validates if a "Restore" operation is allowed for the given tenant status and lifecycle.
     */
    public static function canRestore(string $status, string $lifecycle): bool
    {
        return $status === 'suspended';
    }

    /**
     * Infers the lifecycle state for pre-v1.5 tenants who don't have the 'lifecycle' property in settings.
     */
    public static function inferLifecycle(TenantModel $tenant): string
    {
        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $deployment = $settings['deployment'] ?? null;

        if (! is_array($deployment)) {
            return 'provisioned';
        }

        if (isset($deployment['lifecycle'])) {
            return (string) $deployment['lifecycle'];
        }

        $deployStatus = $deployment['status'] ?? null;
        if ($deployStatus === 'active_on_instance') {
            return 'running';
        }

        if ($deployStatus === 'pending_dedicated_instance') {
            return 'provisioned';
        }

        return 'provisioned';
    }
}
