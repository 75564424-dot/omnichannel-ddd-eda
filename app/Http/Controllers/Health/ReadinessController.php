<?php

declare(strict_types=1);

namespace App\Http\Controllers\Health;

use App\Shared\Platform\Services\InstanceReadinessProbe;
use Illuminate\Http\JsonResponse;

/**
 * Readiness probe for load balancers and orchestrators (Plan_Cloud).
 */
final class ReadinessController
{
    public function __construct(
        private readonly InstanceReadinessProbe $probe,
    ) {}

    public function __invoke(): JsonResponse
    {
        $result = $this->probe->probe();

        return response()->json($result, $result['status'] === 'ready' ? 200 : 503);
    }
}
