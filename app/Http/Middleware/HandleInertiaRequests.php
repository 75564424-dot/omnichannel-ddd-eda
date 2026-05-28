<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Control\Application\Services\ClientIncidentReportService;
use App\Control\Application\Services\ClientInstancePortalService;
use App\Control\Application\Services\InstanceSimulationReadinessService;
use App\Models\User;
use App\Shared\Identity\Domain\PlatformRole;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $role = $user !== null
            ? PlatformRole::tryFromString((string) $user->getAttribute('platform_role'))
            : null;

        $supportUnread = 0;
        if ($user instanceof User && $role !== null && $role->isInstanceOperator()) {
            $supportUnread = app(ClientIncidentReportService::class)->unreadResponsesCountForUser($user);
        }

        $portal = $request->is('control', 'control/*') ? 'control' : 'instance';

        return array_merge(parent::share($request), [
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
                ? app(ClientInstancePortalService::class)->sharedForInertia()
                : null,
            'simulation' => $portal === 'instance'
                ? app(InstanceSimulationReadinessService::class)->sharedForInertia()
                : null,
            'support_unread_count' => $supportUnread,
            'flash' => [
                'message' => $request->session()->get('message'),
            ],
        ]);
    }
}
