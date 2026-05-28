<?php

declare(strict_types=1);

namespace App\Shared\Identity\Domain;

/**
 * Platform operator roles (Plan_Usuarios.md).
 */
enum PlatformRole: string
{
    case SaasAdmin = 'saas_admin';
    case PlatformAdmin = 'platform_admin';
    case BusOperator = 'bus_operator';
    case DashboardViewer = 'dashboard_viewer';

    public function label(): string
    {
        return match ($this) {
            self::SaasAdmin        => 'SaaS Admin',
            self::PlatformAdmin    => 'Platform Admin',
            self::BusOperator      => 'Bus Operator',
            self::DashboardViewer  => 'Dashboard Viewer',
        };
    }

    public function isSaasAdmin(): bool
    {
        return $this === self::SaasAdmin;
    }

    public function isInstanceOperator(): bool
    {
        return $this !== self::SaasAdmin;
    }

    public function canAccessDashboardWeb(): bool
    {
        return match ($this) {
            self::PlatformAdmin, self::DashboardViewer => true,
            default => false,
        };
    }

    public function canAccessMiddlewareWeb(): bool
    {
        return match ($this) {
            self::PlatformAdmin, self::BusOperator => true,
            default => false,
        };
    }

    public static function tryFromString(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom($value);
    }
}
