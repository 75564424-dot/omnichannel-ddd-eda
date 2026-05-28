<?php

declare(strict_types=1);

namespace App\Http\Controllers\Control;

use App\Control\Application\Services\ControlMiddlewareService;
use App\Control\Application\Services\TenantPresentationService;
use App\Monitoring\Application\Services\AlertEvaluationService;
use Inertia\Inertia;
use Inertia\Response;

final class OverviewController
{
    public function __construct(
        private readonly TenantPresentationService $tenants,
        private readonly ControlMiddlewareService $middleware,
        private readonly AlertEvaluationService $alerts,
    ) {}

    public function index(): Response
    {
        $tenantList = $this->tenants->listTenants();
        $middleware = $this->middleware->snapshot();
        $alertList = array_map(
            static fn ($a) => $a->toArray(),
            $this->alerts->evaluate(),
        );

        return Inertia::render('Control/Overview/Index', [
            'tenants'    => $tenantList,
            'middleware' => [
                'bus_status'     => $middleware['metrics']['bus_status'] ?? 'UNKNOWN',
                'latency_ms'     => $middleware['metrics']['latency_ms'] ?? 0,
                'queue_depth'    => $middleware['queues']['depth'] ?? 0,
                'dead_letters'   => $middleware['metrics']['dead_letters'] ?? 0,
            ],
            'alerts_count' => count($alertList),
            'alerts'       => array_slice($alertList, 0, 5),
        ]);
    }
}
