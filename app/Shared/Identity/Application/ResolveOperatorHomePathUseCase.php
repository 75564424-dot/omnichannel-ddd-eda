<?php

declare(strict_types=1);

namespace App\Shared\Identity\Application;

use App\Models\User;
use App\Shared\Identity\Domain\PlatformRole;

final class ResolveOperatorHomePathUseCase
{
    public function execute(User $user): string
    {
        $role = PlatformRole::tryFromString((string) $user->getAttribute('platform_role'));
        if ($role === null) {
            return route('dashboard');
        }

        return match ($role) {
            PlatformRole::SaasAdmin        => route('control.overview'),
            PlatformRole::BusOperator      => route('middleware'),
            PlatformRole::DashboardViewer  => route('dashboard'),
            PlatformRole::PlatformAdmin    => route('dashboard'),
        };
    }
}
