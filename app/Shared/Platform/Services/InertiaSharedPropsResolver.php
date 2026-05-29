<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

use App\Control\Application\Services\ClientIncidentReportService;
use App\Control\Application\Services\ClientInstancePortalService;
use App\Simulation\Application\Services\Prepare\InstanceSimulationReadinessService;
use App\Models\User;
use App\Shared\Identity\Domain\PlatformRole;
use Illuminate\Http\Request;

final class InertiaSharedPropsResolver
{
    public function __construct(
        private readonly ClientIncidentReportService $supportReports,
        private readonly ClientInstancePortalService $instancePortal,
        private readonly InstanceSimulationReadinessService $simulationReadiness,
    ) {}

    /** @return array<string, mixed> */
    public function resolve(Request $request): array
    {
        $user = $request->user();
        $role = $user !== null
            ? PlatformRole::tryFromString((string) $user->getAttribute('platform_role'))
            : null;

        $supportUnread = 0;
        if ($user instanceof User && $role !== null && $role->isInstanceOperator()) {
            $supportUnread = $this->supportReports->unreadResponsesCountForUser($user);
        }

        $portal = $request->is('control', 'control/*') ? 'control' : 'instance';

        return [
            'auth' => [
                'user' => $user !== null ? [
                    'id'            => $user->getAuthIdentifier(),
                    'name'          => $user->getAttribute('name'),
                    'email'         => $user->getAttribute('email'),
                    'platform_role' => $user->getAttribute('platform_role'),
                    'tenant_id'     => $user->getAttribute('tenant_id'),
                    'is_saas_admin' => $role?->isSaasAdmin() ?? false,
                    'can_access_dashboard'  => $role?->canAccessDashboardWeb() ?? false,
                    'can_access_middleware' => $role?->canAccessMiddlewareWeb() ?? false,
                ] : null,
            ],
            'portal' => $portal,
            'instance' => $portal === 'instance'
                ? $this->instancePortal->sharedForInertia()
                : null,
            'simulation' => $portal === 'instance'
                ? $this->simulationReadiness->sharedForInertia()
                : null,
            'support_unread_count' => $supportUnread,
            'flash' => [
                'message' => $request->session()->get('message'),
            ],
            'csrf' => [
                'token'  => csrf_token(),
                'cookie' => (string) config('session.xsrf_cookie', 'XSRF-TOKEN'),
            ],
        ];
    }
}
